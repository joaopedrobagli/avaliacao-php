<?php
// Variáveis recebidas do DashboardController::index():
// $servicos (array), $totalUsuario (float), $pendentesUsuario (array),
// $filtros (array), $sucesso (string|null), $erro (string|null)
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Sistema de Controle de Serviços</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-layout">
        <?php require __DIR__ . '/../layout/sidebar.php'; ?>

        <main class="main-content">
            <h1>DASHBOARD</h1>

            <?php if (!empty($sucesso)): ?>
                <div class="alert alert-sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-erro"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Data atual, pedida explicitamente no enunciado -->
            <p class="data-atual">
                Hoje: <?= date('d/m/Y') ?>
            </p>

            <div class="dashboard-cards">
                <!-- Valor total prestado pelo usuário logado, em destaque -->
                <div class="card card-total">
                    <span>Valor Total dos Meus Serviços</span>
                    <strong>R$ <?= number_format($totalUsuario, 2, ',', '.') ?></strong>
                </div>
            </div>

            <section class="dashboard-columns">
                <div>
                    <h2>Serviços Pendentes</h2>
                    <?php if (empty($pendentesUsuario)): ?>
                        <p class="vazio">Nenhum serviço pendente.</p>
                    <?php else: ?>
                        <ul class="lista-pendentes">
                            <?php foreach ($pendentesUsuario as $p): ?>
                                <li>
                                    #<?= (int) $p['id_service'] ?> -
                                    <?= htmlspecialchars($p['description']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Filtros: GET, pra URL ficar compartilhável/copiável -->
            <form method="GET" action="index.php" class="form-filtros">
                <input type="hidden" name="rota" value="dashboard/index">

                <div class="campo-filtro">
                    <span>Nome do serviço</span>
                    <input
                        type="text"
                        name="descricao"
                        placeholder="Nome do serviço"
                        value="<?= htmlspecialchars($filtros['descricao']) ?>"
                    >
                </div>

                <div class="campo-filtro">
                    <span>Nome do usuário</span>
                    <input
                        type="text"
                        name="usuario"
                        placeholder="Nome do usuário"
                        value="<?= htmlspecialchars($filtros['usuario']) ?>"
                    >
                </div>

                <div class="campo-filtro">
                    <span>Status</span>
                    <select name="status">
                        <option value="">Todos os status</option>
                        <option value="pendente" <?= $filtros['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="finalizado" <?= $filtros['status'] === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                    </select>
                </div>

                <div class="campo-filtro">
                    <span>De</span>
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
                </div>

                <div class="campo-filtro">
                    <span>Até</span>
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($filtros['data_fim']) ?>">
                </div>

                <button type="submit">Filtrar</button>
            </form>

            <table class="tabela-servicos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Valor</th>
                        <th>Comissão</th>
                        <th>Usuário</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servicos)): ?>
                        <tr>
                            <td colspan="7" class="vazio">Nenhum serviço encontrado.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($servicos as $s): ?>
                        <?php
                        // Status é derivado de finished_at, não existe
                        // coluna "status" física na tabela (ver Service::findAll).
                        $finalizado = $s['finished_at'] !== null;
                        ?>
                        <tr>
                            <td><?= (int) $s['id_service'] ?></td>
                            <td><?= htmlspecialchars($s['description']) ?></td>
                            <td>
                                <span class="badge <?= $finalizado ? 'badge-finalizado' : 'badge-pendente' ?>">
                                    <?= $finalizado ? 'Finalizado' : 'Pendente' ?>
                                </span>
                            </td>
                            <td>R$ <?= number_format((float) $s['price'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($finalizado): ?>
                                    R$ <?= number_format((float) $s['commission_user'], 2, ',', '.') ?>
                                <?php else: ?>
                                    <span class="vazio">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($s['user_name']) ?></td>
                            <td class="acoes">
                                <a href="index.php?rota=service/edit&id=<?= (int) $s['id_service'] ?>">Alterar</a>

                                <form method="POST" action="index.php?rota=dashboard/excluir" class="form-inline"
                                      onsubmit="return confirm('Excluir este serviço?');">
                                    <input type="hidden" name="id" value="<?= (int) $s['id_service'] ?>">
                                    <button type="submit" class="btn-link btn-excluir">Excluir</button>
                                </form>

                                <?php if (!$finalizado): ?>
                                    <form method="POST" action="index.php?rota=dashboard/finalizar" class="form-inline"
                                          onsubmit="return confirm('Finalizar este serviço? Um email será enviado.');">
                                        <input type="hidden" name="id" value="<?= (int) $s['id_service'] ?>">
                                        <button type="submit" class="btn-link btn-finalizar">Finalizar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script src="assets/js/dashboard.js"></script>
</body>
</html>