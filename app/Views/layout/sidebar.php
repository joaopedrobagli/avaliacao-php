<?php
// Este arquivo é um "partial": não é uma página completa, é um pedaço
// de HTML que outras views vão incluir com require/include.
// Ele espera que $_SESSION já tenha 'user_name' (usuário logado).
?>
<aside class="sidebar">
    <div class="sidebar-user">
        <span>Logado como:</span>
        <strong><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></strong>
    </div>

    <nav class="sidebar-nav">
        <a href="index.php?rota=service/create">Cadastrar Serviço</a>
        <a href="index.php?rota=auth/logout">Sair</a>
    </nav>
</aside>
