<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Service;

class ServiceController extends Controller
{
    private Service $serviceModel;

    public function __construct()
    {
        $this->serviceModel = new Service();
    }

    /**
     * GET service/create
     * Cenário BDD: "clicar no botão de adicionar novo serviço"
     */
    public function showCreate(): void
    {
        $this->requireLogin();

        $this->render('services/create');
    }

    /**
     * POST service/create
     * Cenários BDD:
     *  - sucesso -> cadastra com status "Pendente", mensagem de sucesso,
     *               redireciona pro Dashboard
     *  - falha   -> não cadastra, mensagem de falha, redireciona
     *               pro Dashboard (conforme o enunciado pede)
     */
    public function store(): void
    {
        $this->requireLogin();

        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');

        // Validação dos campos obrigatórios, conforme o BDD.
        if ($description === '' || $price === '' || !is_numeric($price)) {
            // redirect() já usa caminho relativo; aqui mandamos a
            // mensagem de erro via query string pro Dashboard mostrar.
            $this->redirect('dashboard/index&erro=' . urlencode('Falha ao cadastrar serviço. Verifique os dados.'));
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $this->serviceModel->create($description, (float) $price, $userId);

        $this->redirect('dashboard/index&sucesso=' . urlencode('Serviço cadastrado com sucesso!'));
    }

    /**
     * GET service/edit?id=X
     * Tela de "alterar registro" (botão da tabela do Dashboard).
     */
    public function showEdit(): void
    {
        $this->requireLogin();

        $id = (int) ($_GET['id'] ?? 0);
        $servico = $this->serviceModel->findById($id);

        if (!$servico) {
            $this->redirect('dashboard/index');
            return;
        }

        $this->render('services/edit', ['servico' => $servico]);
    }

    /**
     * POST service/edit
     */
    public function update(): void
    {
        $this->requireLogin();

        $id = (int) ($_POST['id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');

        if ($description === '' || $price === '' || !is_numeric($price)) {
            $this->redirect('service/edit&id=' . $id . '&erro=' . urlencode('Preencha todos os campos corretamente.'));
            return;
        }

        $this->serviceModel->update($id, $description, (float) $price);

        $this->redirect('dashboard/index&sucesso=' . urlencode('Serviço atualizado com sucesso!'));
    }
}
