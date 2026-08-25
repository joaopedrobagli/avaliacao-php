<?php

namespace App\Core;

/**
 * Classe base que os outros Controllers vão herdar (extends).
 * Centraliza tarefas repetitivas, como carregar uma view com dados.
 */
abstract class Controller
{
    /**
     * Bloqueia o acesso de quem não está logado, redirecionando pro login.
     * Chame isso no início de qualquer método de Controller que exija
     * autenticação (Dashboard, cadastro de serviço, etc).
     */
    protected function requireLogin(): void
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    /**
     * Carrega um arquivo de view passando variáveis pra ele.
     *
     * @param string $view Caminho relativo dentro de app/Views, ex: 'auth/login'
     * @param array $data  Dados que a view vai poder usar (viram variáveis soltas)
     */
    protected function render(string $view, array $data = []): void
    {
        // extract() transforma ['erro' => 'x'] na variável $erro dentro da view.
        // É uma forma simples de "passar dados" sem precisar de template engine.
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            die('View não encontrada: ' . htmlspecialchars($view));
        }

        require $viewPath;
    }

    /**
     * Redireciona para outra rota do próprio sistema e encerra o script.
     * Ex.: $this->redirect('dashboard/index');
     */
    protected function redirect(string $rota): void
    {
        // Caminho RELATIVO (sem "/" no início). Assim funciona tanto em
        // http://localhost/index.php quanto em
        // http://localhost/avaliacao-php/public/index.php,
        // porque o navegador resolve a partir da pasta atual.
        header('Location: index.php?rota=' . $rota);
        exit;
    }
}
