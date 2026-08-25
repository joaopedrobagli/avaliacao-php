<?php
// $servico vem de ServiceController::showEdit()
// $erro pode vir via query string, ver ServiceController::update()
$erro = $_GET['erro'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Serviço</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php require __DIR__ . '/../layout/sidebar.php'; ?>

        <main class="main-content">
            <div class="container-form container-form--inline">
                <h1>Alterar Serviço #<?= (int) $servico['id_service'] ?></h1>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php?rota=service/edit">
                    <input type="hidden" name="id" value="<?= (int) $servico['id_service'] ?>">

                    <input
                        type="text"
                        name="description"
                        value="<?= htmlspecialchars($servico['description']) ?>"
                        required
                    >

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="price"
                        value="<?= htmlspecialchars((string) $servico['price']) ?>"
                        required
                    >

                    <div class="form-actions">
                        <button type="submit">Salvar</button>
                        <a href="index.php?rota=dashboard/index">Cancelar</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
