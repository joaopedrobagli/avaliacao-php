<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * Controller = "maestro". Recebe o que o usuário enviou (GET/POST),
 * decide o que fazer (chamando o Model quando precisa de dado do banco),
 * e decide qual view mostrar em seguida.
 *
 * Regra que é seguido aqui: Controller NUNCA escreve SQL. Quem faz isso
 * é o Model (App\Models\User).
 */
class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * GET auth/login
     * Cenário BDD: "usuário acesse a tela de login"
     * Só mostra o formulário. Nenhuma regra de negócio aqui.
     */
    public function showLogin(): void
    {
        // Se já está logado, não faz sentido ver o login de novo.
        if (isset($_SESSION['user_id'])) {
            $this->redirect('dashboard/index');
        }

        $this->render('auth/login');
    }

    /**
     * POST auth/login
     * Cenários BDD:
     *  - email/senha inválidos -> mensagem 'Ops, Email ou Senha inválido'
     *  - email/senha válidos   -> redireciona pro Dashboard
     */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['password'] ?? '');

        // Validação básica: campos vazios já contam como "inválido".
        if ($email === '' || $senha === '') {
            $this->render('auth/login', [
                'erro' => 'Ops, Email ou Senha inválido',
            ]);
            return;
        }

        $usuario = $this->userModel->findByEmail($email);

        // Compara com MD5 porque é assim que a senha foi guardada
        // (lembrando: password é VARCHAR(45), não comporta bcrypt).
        $senhaCorreta = $usuario && $usuario['password'] === md5($senha);

        if (!$usuario || !$senhaCorreta) {
            $this->render('auth/login', [
                'erro' => 'Ops, Email ou Senha inválido',
            ]);
            return;
        }

        // Login OK: guardamos o essencial na sessão.
        // Evitamos guardar a senha na sessão, mesmo com hash.
        $_SESSION['user_id'] = $usuario['id_user'];
        $_SESSION['user_name'] = $usuario['name'];

        $this->redirect('dashboard/index');
    }

    /**
     * GET auth/register
     * Tela "Cadastrar usuário" do wireframe.
     */
    public function showRegister(): void
    {
        $this->render('auth/register');
    }

    /**
     * POST auth/register
     * Cria o usuário e já manda pro login (o wireframe não pede
     * login automático, então deixamos o fluxo explícito).
     */
    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $senha === '') {
            $this->render('auth/register', [
                'erro' => 'Preencha todos os campos.',
            ]);
            return;
        }

        if ($this->userModel->emailExists($email)) {
            $this->render('auth/register', [
                'erro' => 'Este email já está cadastrado.',
            ]);
            return;
        }

        $this->userModel->create($name, $email, $senha);

        $this->render('auth/login', [
            'sucesso' => 'Usuário cadastrado com sucesso! Faça login.',
        ]);
    }

    /**
     * Encerra a sessão e volta pro login.
     */
    public function logout(): void
    {
        session_destroy();
        $this->redirect('auth/login');
    }
}
