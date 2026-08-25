<?php
// Recebe $erro opcionalmente, vindo de AuthController::register()
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container-form">
        <h1>Cadastrar Novo Usuário</h1>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?rota=auth/register">
            <input
                type="text"
                name="name"
                placeholder="nome completo"
                required
            >

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
                <button type="submit">Cadastrar</button>
                <a href="index.php?rota=auth/login">Já tenho conta</a>
            </div>
        </form>
    </div>
</body>
</html>
