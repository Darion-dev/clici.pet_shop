<?php
require __DIR__ . '/../includes/funcoes.php';
require __DIR__ . '/../config/conexao.php';

exigir_admin();

$servicos = $pdo->query(
    'SELECT id, nome, descricao, preco, duracao_min, ativo FROM servicos ORDER BY nome'
)->fetchAll();

$titulo = 'Admin - Serviços';
require __DIR__ . '/../includes/cabecalho.php';
?>
<main>
    <div class="subnav">
        <a href="/admin/index.php">Agendamentos</a>
        <a class="ativo" href="/admin/servicos.php">Serviços</a>
        <a href="/admin/produtos.php">Produtos</a>
    </div>

    <div class="secao-titulo">
        <h2>Serviços</h2>
        <a class="btn" href="/admin/servico_form.php">+ Novo serviço</a>
    </div>

    <?php if (!$servicos): ?>
        <div class="vazio"><span class="emoji">🛁</span>Nenhum serviço cadastrado.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Serviço</th><th>Preço</th><th>Duração</th><th>Situação</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($servicos as $s): ?>
                    <tr>
                        <td><strong><?= icone_servico($s['nome']) ?> <?= e($s['nome']) ?></strong><br><small><?= e($s['descricao']) ?></small></td>
                        <td><?= e(preco_br($s['preco'])) ?></td>
                        <td><?= (int) $s['duracao_min'] ?> min</td>
                        <td><span class="status <?= $s['ativo'] ? 'confirmado' : 'cancelado' ?>"><?= $s['ativo'] ? 'ativo' : 'inativo' ?></span></td>
                        <td>
                            <a class="btn mini sec" href="/admin/servico_form.php?id=<?= (int) $s['id'] ?>">Editar</a>
                            <form class="inline" action="/admin/servico_excluir.php" method="post"
                                  onsubmit="return confirm('Excluir este serviço?');">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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
<?php require __DIR__ . '/../includes/rodape.php'; ?>
