<?php
require __DIR__. '/../includes/funcoes.php';
require __DIR__. '/../config/conexao.php';

exigir_admin();

$id      = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$produto = ['nome' => '', 'descricao' => '', 'preco' => '', 'estoque' => 0];

if ($id) {
    $consulta = $pdo->prepare('SELECT * FROM produtos WHERE id = :id');
    $consulta->execute([':id' => $id]);
    $achado = $consulta->fetch();

    if (!$achado) {
        flash('Produto não encontrado.', 'erro');
        redirecionar('/admin/produtos.php');
    }
    $produto = $achado;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $produto['nome']      = trim($_POST['nome'] ?? '');
    $produto['descricao'] = trim($_POST['descricao'] ?? '');
    $produto['preco']     = trim($_POST['preco'] ?? '');
    $produto['estoque']   = (int) ($_POST['estoque'] ?? 0);

    // Aceita vírgula como separador decimal (ex.: 49,90).
    $preco = str_replace(',', '.', $produto['preco']);

    if ($produto['nome'] === '') {
        $erros[] = 'Informe o nome do produto.';
    }
    if (!is_numeric($preco) || (float) $preco <= 0) {
        $erros[] = 'Informe um preço válido.';
    }
    if ($produto['estoque'] < 0) {
        $erros[] = 'O estoque não pode ser negativo.';
    }

    if (!$erros) {
        $dados = [
            ':nome'      => $produto['nome'],
            ':descricao' => $produto['descricao'] !== '' ? $produto['descricao'] : null,
            ':preco'     => (float) $preco,
            ':estoque'   => $produto['estoque'],
        ];

        try {
            if ($id) {
                $sql = 'UPDATE produtos SET nome = :nome, descricao = :descricao,
                               preco = :preco, estoque = :estoque WHERE id = :id';
                $dados[':id'] = $id;
            } else {
                $sql = 'INSERT INTO produtos (nome, descricao, preco, estoque)
                        VALUES (:nome, :descricao, :preco, :estoque)';
            }
            $pdo->prepare($sql)->execute($dados);

            flash($id ? 'Produto atualizado.' : 'Produto cadastrado.');
            redirecionar('/admin/produtos.php');
        } catch (PDOException $ex) {
            $erros[] = 'Não foi possível salvar o produto.';
        }
    }
}

$titulo = $id ? 'Editar produto' : 'Novo produto';
require __DIR__. '/../includes/cabecalho.php';
?>
<main>
    <form class="box centro" action="/admin/produto_form.php" method="post" novalidate>
        <h2><?= $id ? 'Editar produto' : 'Novo produto' ?></h2>

        <?php if ($erros): ?>
            <div class="msg erro" role="alert">
                <?php foreach ($erros as $erro): ?><p><?= e($erro) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="nome">Nome</label>
        <input id="nome" name="nome" type="text" value="<?= e($produto['nome']) ?>" required>

        <label for="descricao">Descrição</label>
        <input id="descricao" name="descricao" type="text" maxlength="255" value="<?= e($produto['descricao'] ?? '') ?>">

        <label for="preco">Preço (R$)</label>
        <input id="preco" name="preco" type="text" inputmode="decimal" value="<?= e($produto['preco']) ?>" required>

        <label for="estoque">Estoque (unidades)</label>
        <input id="estoque" name="estoque" type="number" min="0" value="<?= (int) $produto['estoque'] ?>">

        <button class="btn" type="submit">Salvar</button>
        <p class="auth-extra"><a href="/admin/produtos.php">Voltar</a></p>
    </form>
</main>
<?php require __DIR__. '/../includes/rodape.php'; ?>