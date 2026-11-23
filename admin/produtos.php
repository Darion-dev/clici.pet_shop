<?php
require __DIR__ . '/../includes/funcoes.php';
require __DIR__ . '/../config/conexao.php';

exigir_admin();

$produtos = $pdo->query(
    'SELECT id, nome, descricao, preco, estoque FROM produtos ORDER BY nome'
)->fetchAll();

$titulo = 'Admin - Produtos';
require __DIR__. '/../includes/cabecalho.php';
?>
<main>
    <div class="subnav">
        <a href="/admin/index.php">Agendamentos</a>
        <a href="/admin/servicos.php">Serviços</a>
        <a class="ativo" href="/admin/produtos.php">Produtos</a>
    </div>

    <div class="secao-titulo">
        <h2>Produtos</h2>
        <a class="btn" href="/admin/produto_form.php">+ Novo produto</a>
    </div>

    <?php if (!$produtos): ?>
        <div class="vazio">Nenhum produto cadastrado.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Produto</th><th>Preço</th><th>Estoque</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><strong><?= e($p['nome']) ?></strong></td>
                        <td><?= e(preco_br($p['preco'])) ?></td>
                        <td><?= (int) $p['estoque'] ?></td>
                        <td>
                            <a class="btn mini sec" href="/admin/produto_form.php?id=<?= (int) $p['id'] ?>">Editar</a>
                            <form class="inline" action="/admin/produto_excluir.php" method="post"
                                  onsubmit="return confirm('Excluir este produto?');">
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
<?php require __DIR__ . '/../includes/rodape.php'; ?>