<?php
// View simples, sem variáveis obrigatórias vindas do Controller.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Novo Serviço</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php require __DIR__ . '/../layout/sidebar.php'; ?>

        <main class="main-content">
            <div class="container-form container-form--inline">
                <h1>Cadastrar Novo Serviço</h1>

                <form method="POST" action="index.php?rota=service/create">
                    <input
                        type="text"
                        name="description"
                        placeholder="descrição"
                        required
                    >

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="price"
                        placeholder="preço"
                        required
                    >

                    <div class="form-actions">
                        <button type="submit">Cadastrar</button>
                        <a href="index.php?rota=dashboard/index">Voltar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
