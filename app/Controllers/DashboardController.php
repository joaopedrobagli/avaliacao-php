<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\SmtpMailer;
use App\Config\Mail;
use App\Models\Service;
use Exception;

class DashboardController extends Controller
{
    private Service $serviceModel;

    public function __construct()
    {
        $this->serviceModel = new Service();
    }

    /**
     * GET dashboard/index
     * Reúne tudo que o wireframe pede na tela principal:
     * - tabela de serviços (com filtros opcionais vindos da URL)
     * - valor total prestado pelo usuário logado
     * - lista de pendentes do usuário logado
     */
    public function index(): void
    {
        $this->requireLogin();

        $userId = (int) $_SESSION['user_id'];

        // $_GET porque os filtros vêm de um <form method="GET">:
        // isso deixa a URL "compartilhável" (dá pra copiar o link filtrado).
        $filtros = [
            'data_inicio' => $_GET['data_inicio'] ?? '',
            'data_fim'    => $_GET['data_fim'] ?? '',
            'descricao'   => $_GET['descricao'] ?? '',
            'status'      => $_GET['status'] ?? '',
            'usuario'     => $_GET['usuario'] ?? '',
        ];

        $servicos = $this->serviceModel->findAll($filtros);
        $totalUsuario = $this->serviceModel->totalByUser($userId);
        $pendentesUsuario = $this->serviceModel->pendingByUser($userId);

        $this->render('dashboard/index', [
            'servicos' => $servicos,
            'totalUsuario' => $totalUsuario,
            'pendentesUsuario' => $pendentesUsuario,
            'filtros' => $filtros,
            'sucesso' => $_GET['sucesso'] ?? null,
            'erro' => $_GET['erro'] ?? null,
        ]);
    }

    /**
     * POST dashboard/finalizar
     * Cenário BDD: marcar status como finalizado.
     * Grava finished_at, calcula comissão e envia email pro usuário
     * dono do serviço.
     */
    public function finalizar(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $servico = $this->serviceModel->finish($id);

        if ($servico) {
            $this->enviarEmailFinalizacao($servico);
        }

        $this->redirect('dashboard/index');
    }

    /**
     * POST dashboard/excluir
     */
    public function excluir(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $this->serviceModel->delete($id);

        $this->redirect('dashboard/index');
    }

    /**
     * Envia o email de aviso de finalização via SMTP autenticado (Gmail).
     * Se o envio falhar por qualquer motivo NÃO trava o fluxo de finalizar o serviço -
     * só registra o erro.
     */
    private function enviarEmailFinalizacao(array $servico): void
    {
        $assunto = 'Serviço finalizado - #' . $servico['id_service'];

        $corpo = "Olá, {$servico['user_name']}!\n\n"
            . "O serviço \"{$servico['description']}\" foi finalizado.\n"
            . "Valor: R$ " . number_format((float) $servico['price'], 2, ',', '.') . "\n"
            . "Comissão: R$ " . number_format((float) $servico['commission_user'], 2, ',', '.') . "\n";

        try {
            $mailer = new SmtpMailer(
                Mail::SMTP_HOST,
                Mail::SMTP_PORT,
                Mail::GMAIL_USER,
                Mail::GMAIL_APP_PASSWORD
            );

            $mailer->send($servico['user_email'], $assunto, $corpo);
        } catch (Exception $e) {
            // Em produção isso iria para um arquivo de log.
            // Aqui só evita que o erro quebre a tela do usuário.
            error_log('Falha ao enviar email de finalização: ' . $e->getMessage());
        }
    }
}