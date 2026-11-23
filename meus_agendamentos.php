<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

exigir_login();
$u = usuario_atual();

// Cancelar um agendamento (só o dono, e só se ainda estiver ativo).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $cancelar = $pdo->prepare(
        "UPDATE agendamentos a
           JOIN pets p ON p.id = a.pet_id
            SET a.status = 'cancelado'
          WHERE a.id = :id AND p.usuario_id = :u
            AND a.status IN ('pendente', 'confirmado')"
    );
    $cancelar->execute([':id' => (int) ($_POST['id'] ?? 0), ':u' => $u['id']]);

    flash('Agendamento cancelado.');
    redirecionar('/meus_agendamentos.php');
}

$consulta = $pdo->prepare(
    'SELECT a.id, a.data_hora, a.status, a.observacao,
            p.nome AS pet, s.nome AS servico, s.preco
       FROM agendamentos a
       JOIN pets p     ON p.id = a.pet_id
       JOIN servicos s ON s.id = a.servico_id
      WHERE p.usuario_id = :u
      ORDER BY a.data_hora DESC'
);
$consulta->execute([':u' => $u['id']]);
$agendamentos = $consulta->fetchAll();

$titulo = 'Agendamentos';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <div class="secao-titulo">
        <h2>Meus agendamentos</h2>
        <a class="btn" href="/agendar.php">+ Novo agendamento</a>
    </div>

    <?php if (!$agendamentos): ?>
        <div class="vazio"><span class="emoji">📅</span>Você ainda não tem agendamentos.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Data</th><th>Pet</th><th>Serviço</th><th>Valor</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($agendamentos as $a): ?>
                    <tr>
                        <td><?= e(data_br($a['data_hora'], true)) ?></td>
                        <td><strong><?= e($a['pet']) ?></strong></td>
                        <td><?= e($a['servico']) ?></td>
                        <td><?= e(preco_br($a['preco'])) ?></td>
                        <td><span class="status <?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                        <td>
                            <?php if (in_array($a['status'], ['pendente', 'confirmado'], true)): ?>
                                <form class="inline" action="/meus_agendamentos.php" method="post"
                                      onsubmit="return confirm('Cancelar este agendamento?');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                    <button class="btn mini perigo" type="submit">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
