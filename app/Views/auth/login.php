<?php
// Esta view recebe (opcionalmente) as variáveis $erro e $sucesso,
// passadas pelo Controller via $this->render('auth/login', [...]).
// Como usamos extract() no Controller, elas já chegam prontas aqui.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Controle de Serviços</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-form">
        <h1>Sistema de Controle de Serviços</h1>

        <?php if (!empty($erro)): ?>
            <!-- htmlspecialchars evita que o texto vire HTML/JS malicioso -->
            <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <?php endif; ?>

        <!-- action aponta pra rota que o Router vai interpretar como
             AuthController::login (ver public/index.php) -->
        <form method="POST" action="index.php?rota=auth/login">
            <input
                type="email"
                name="email"
                placeholder="email@email.com"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="**************"
                required
            >

            <div class="form-actions">
                <button type="submit">Entrar</button>
                <a href="index.php?rota=auth/register">Cadastrar usuário</a>
            </div>
        </form>
    </div>
</body>
</html>
