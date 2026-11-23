<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

exigir_login();
$u = usuario_atual();

$consulta = $pdo->prepare(
    'SELECT id, nome, especie, raca, nascimento
       FROM pets WHERE usuario_id = :u ORDER BY nome'
);
$consulta->execute([':u' => $u['id']]);
$pets = $consulta->fetchAll();

$titulo = 'Meus pets';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <div class="secao-titulo">
        <h2>Meus pets</h2>
        <a class="btn" href="/pet_form.php">+ Novo pet</a>
    </div>

    <?php if (!$pets): ?>
        <div class="vazio">
            <span class="emoji">🐶</span>
            Você ainda não cadastrou nenhum pet.
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Nome</th><th>Espécie</th><th>Raça</th><th>Nascimento</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($pets as $p): ?>
                    <tr>
                        <td><strong><?= e($p['nome']) ?></strong></td>
                        <td><?= e($p['especie']) ?></td>
                        <td><?= e($p['raca'] ?: '-') ?></td>
                        <td><?= e(data_br($p['nascimento'])) ?></td>
                        <td>
                            <a class="btn mini menta" href="/agendar.php?pet=<?= (int) $p['id'] ?>">Agendar</a>
                            <a class="btn mini sec" href="/pet_form.php?id=<?= (int) $p['id'] ?>">Editar</a>
                            <form class="inline" action="/pet_excluir.php" method="post"
                                  onsubmit="return confirm('Remover este pet e seus agendamentos?');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn mini perigo" type="submit">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
