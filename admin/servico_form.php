<?php
require __DIR__ . '/../includes/funcoes.php';
require __DIR__ . '/../config/conexao.php';

exigir_admin();

$id      = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$servico = ['nome' => '', 'descricao' => '', 'preco' => '', 'duracao_min' => 60, 'ativo' => 1];

if ($id) {
    $consulta = $pdo->prepare('SELECT * FROM servicos WHERE id = :id');
    $consulta->execute([':id' => $id]);
    $achado = $consulta->fetch();

    if (!$achado) {
        flash('Serviço não encontrado.', 'erro');
        redirecionar('/admin/servicos.php');
    }
    $servico = $achado;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $servico['nome']        = trim($_POST['nome'] ?? '');
    $servico['descricao']   = trim($_POST['descricao'] ?? '');
    $servico['preco']       = trim($_POST['preco'] ?? '');
    $servico['duracao_min'] = (int) ($_POST['duracao_min'] ?? 0);
    $servico['ativo']       = isset($_POST['ativo']) ? 1 : 0;

    // Aceita vírgula como separador decimal (ex.: 49,90).
    $preco = str_replace(',', '.', $servico['preco']);

    if ($servico['nome'] === '') {
        $erros[] = 'Informe o nome do serviço.';
    }
    if (!is_numeric($preco) || (float) $preco <= 0) {
        $erros[] = 'Informe um preço válido.';
    }
    if ($servico['duracao_min'] < 5 || $servico['duracao_min'] > 480) {
        $erros[] = 'A duração deve ficar entre 5 e 480 minutos.';
    }

    if (!$erros) {
        $dados = [
            ':nome'      => $servico['nome'],
            ':descricao' => $servico['descricao'] !== '' ? $servico['descricao'] : null,
            ':preco'     => (float) $preco,
            ':duracao'   => $servico['duracao_min'],
            ':ativo'     => $servico['ativo'],
        ];

        try {
            if ($id) {
                $sql = 'UPDATE servicos SET nome = :nome, descricao = :descricao, preco = :preco,
                               duracao_min = :duracao, ativo = :ativo WHERE id = :id';
                $dados[':id'] = $id;
            } else {
                $sql = 'INSERT INTO servicos (nome, descricao, preco, duracao_min, ativo)
                        VALUES (:nome, :descricao, :preco, :duracao, :ativo)';
            }
            $pdo->prepare($sql)->execute($dados);

            flash('Serviço salvo com sucesso!');
            redirecionar('/admin/servicos.php');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $erros[] = 'Não foi possível salvar o serviço.';
        }
    }
}

$titulo = $id ? 'Editar serviço' : 'Novo serviço';
require __DIR__ . '/../includes/cabecalho.php';
?>
<main>
    <form class="box centro" action="/admin/servico_form.php" method="post" novalidate>
        <h2><?= $id ? 'Editar serviço' : 'Novo serviço' ?></h2>

        <?php if ($erros): ?>
            <div class="msg erro" role="alert">
                <?php foreach ($erros as $erro): ?><p><?= e($erro) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="nome">Nome</label>
        <input id="nome" name="nome" type="text" value="<?= e($servico['nome']) ?>" required>

        <label for="descricao">Descrição</label>
        <input id="descricao" name="descricao" type="text" maxlength="255" value="<?= e($servico['descricao']) ?>">

        <label for="preco">Preço (R$)</label>
        <input id="preco" name="preco" type="text" inputmode="decimal" value="<?= e($servico['preco']) ?>" required>

        <label for="duracao_min">Duração (minutos)</label>
        <input id="duracao_min" name="duracao_min" type="number" min="5" max="480" value="<?= (int) $servico['duracao_min'] ?>" required>

        <label class="check">
            <input type="checkbox" name="ativo" <?= $servico['ativo'] ? 'checked' : '' ?>> Serviço ativo (aparece para os clientes)
        </label>

        <button class="btn" type="submit">Salvar</button>
        <p class="auth-extra"><a href="/admin/servicos.php">Voltar</a></p>
    </form>
</main>
<?php require __DIR__ . '/../includes/rodape.php'; ?>
