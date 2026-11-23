<?php
require __DIR__ . '/../includes/funcoes.php';
require __DIR__ . '/../config/conexao.php';

exigir_admin();

// Muda o status de um agendamento (confirmar, concluir ou cancelar).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $novo = $_POST['status'] ?? '';
    if (in_array($novo, ['confirmado', 'concluido', 'cancelado'], true)) {
        $atualizar = $pdo->prepare('UPDATE agendamentos SET status = :s WHERE id = :id');
        $atualizar->execute([':s' => $novo, ':id' => (int) ($_POST['id'] ?? 0)]);
        flash('Status atualizado.');
    }
    redirecionar('/admin/index.php');
}

$agendamentos = $pdo->query(
    'SELECT a.id, a.data_hora, a.status, a.observacao,
            p.nome AS pet, u.nome AS tutor, s.nome AS servico
       FROM agendamentos a
       JOIN pets p     ON p.id = a.pet_id
       JOIN usuarios u ON u.id = p.usuario_id
       JOIN servicos s ON s.id = a.servico_id
      ORDER BY a.data_hora DESC
      LIMIT 200'
)->fetchAll();

$titulo = 'Admin - Agendamentos';
require __DIR__ . '/../includes/cabecalho.php';
?>
<main>
    <div class="subnav">
        <a class="ativo" href="/admin/index.php">Agendamentos</a>
        <a href="/admin/servicos.php">Serviços</a>
        <a href="/admin/produtos.php">Produtos</a>
    </div>
    <h2>Agendamentos</h2>

    <?php if (!$agendamentos): ?>
        <div class="vazio"><span class="emoji">📅</span>Nenhum agendamento ainda.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Data</th><th>Tutor</th><th>Pet</th><th>Serviço</th><th>Status</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($agendamentos as $a): ?>
                    <tr>
                        <td><?= e(data_br($a['data_hora'], true)) ?></td>
                        <td><?= e($a['tutor']) ?></td>
                        <td><strong><?= e($a['pet']) ?></strong></td>
                        <td><?= e($a['servico']) ?></td>
                        <td><span class="status <?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                        <td>
                            <?php foreach (['confirmado' => 'Confirmar', 'concluido' => 'Concluir', 'cancelado' => 'Cancelar'] as $valor => $rotulo): ?>
                                <?php if ($a['status'] !== $valor && !in_array($a['status'], ['cancelado', 'concluido'], true)): ?>
                                    <form class="inline" action="/admin/index.php" method="post">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                        <input type="hidden" name="status" value="<?= e($valor) ?>">
                                        <button class="btn mini <?= $valor === 'cancelado' ? 'perigo' : 'menta' ?>" type="submit"><?= e($rotulo) ?></button>
                                    </form>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
