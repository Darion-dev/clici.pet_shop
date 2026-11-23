<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

exigir_login();
$u = usuario_atual();

$id  = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$pet = ['nome' => '', 'especie' => 'Cachorro', 'raca' => '', 'nascimento' => ''];

// Editando: carrega o pet, mas só se ele pertencer ao usuário logado.
if ($id) {
    $consulta = $pdo->prepare('SELECT * FROM pets WHERE id = :id AND usuario_id = :u');
    $consulta->execute([':id' => $id, ':u' => $u['id']]);
    $achado = $consulta->fetch();

    if (!$achado) {
        flash('Pet não encontrado.', 'erro');
        redirecionar('/pets.php');
    }
    $pet = $achado;
}

$especies = ['Cachorro', 'Gato', 'Ave', 'Roedor', 'Outro'];
$erros    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $pet['nome']       = trim($_POST['nome'] ?? '');
    $pet['especie']    = $_POST['especie'] ?? '';
    $pet['raca']       = trim($_POST['raca'] ?? '');
    $pet['nascimento'] = trim($_POST['nascimento'] ?? '');

    if ($pet['nome'] === '') {
        $erros[] = 'Informe o nome do pet.';
    }
    if (!in_array($pet['especie'], $especies, true)) {
        $erros[] = 'Escolha uma espécie válida.';
    }
    if ($pet['nascimento'] !== '') {
        $data = DateTime::createFromFormat('Y-m-d', $pet['nascimento']);
        if (!$data || $data->format('Y-m-d') !== $pet['nascimento'] || $data > new DateTime()) {
            $erros[] = 'Informe uma data de nascimento válida.';
        }
    }

    if (!$erros) {
        $dados = [
            ':nome'       => $pet['nome'],
            ':especie'    => $pet['especie'],
            ':raca'       => $pet['raca'] !== '' ? $pet['raca'] : null,
            ':nascimento' => $pet['nascimento'] !== '' ? $pet['nascimento'] : null,
        ];

        try {
            if ($id) {
                $sql = 'UPDATE pets SET nome = :nome, especie = :especie, raca = :raca,
                               nascimento = :nascimento
                         WHERE id = :id AND usuario_id = :u';
                $dados[':id'] = $id;
                $dados[':u']  = $u['id'];
            } else {
                $sql = 'INSERT INTO pets (usuario_id, nome, especie, raca, nascimento)
                        VALUES (:u, :nome, :especie, :raca, :nascimento)';
                $dados[':u'] = $u['id'];
            }
            $pdo->prepare($sql)->execute($dados);

            flash('Pet salvo com sucesso!');
            redirecionar('/pets.php');
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $erros[] = 'Não foi possível salvar o pet. Tente novamente.';
        }
    }
}

$titulo = $id ? 'Editar pet' : 'Novo pet';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <form class="box centro" action="/pet_form.php" method="post" novalidate>
        <h2><?= $id ? 'Editar pet' : 'Cadastrar pet' ?></h2>

        <?php if ($erros): ?>
            <div class="msg erro" role="alert">
                <?php foreach ($erros as $erro): ?><p><?= e($erro) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <label for="nome">Nome</label>
        <input id="nome" name="nome" type="text" value="<?= e($pet['nome']) ?>" required>

        <label for="especie">Espécie</label>
        <select id="especie" name="especie">
            <?php foreach ($especies as $esp): ?>
                <option value="<?= e($esp) ?>" <?= $pet['especie'] === $esp ? 'selected' : '' ?>><?= e($esp) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="raca">Raça (opcional)</label>
        <input id="raca" name="raca" type="text" value="<?= e($pet['raca']) ?>">

        <label for="nascimento">Nascimento (opcional)</label>
        <input id="nascimento" name="nascimento" type="date" max="<?= date('Y-m-d') ?>" value="<?= e($pet['nascimento']) ?>">

        <button class="btn" type="submit">Salvar</button>
        <p class="auth-extra"><a href="/pets.php">Voltar</a></p>
    </form>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
