<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

if (logado()) {
    redirecionar('/index.php');
}

$erros = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    try {
        $consulta = $pdo->prepare(
            'SELECT id, nome, senha, e_admin FROM usuarios WHERE email = :email LIMIT 1'
        );
        $consulta->execute([':email' => $email]);
        $usuario = $consulta->fetch();

        // password_verify compara a senha digitada com o hash do banco.
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            session_regenerate_id(true);
            $_SESSION['usuario'] = [
                'id'      => (int) $usuario['id'],
                'nome'    => $usuario['nome'],
                'e_admin' => (int) $usuario['e_admin'],
            ];
            redirecionar($usuario['e_admin'] ? '/admin/index.php' : '/pets.php');
        }

        $erros[] = 'E-mail ou senha incorretos.';
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $erros[] = 'Não foi possível entrar agora. Tente novamente mais tarde.';
    }
}

$titulo = 'Entrar';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <form class="box centro" action="/login.php" method="post" novalidate>
        <h2>Bem-vindo de volta!</h2>
        <p class="sub">Entre para ver seus pets e agendamentos.</p>

        <?php if ($erros): ?>
            <div class="msg erro" role="alert">
                <?php foreach ($erros as $erro): ?>
                    <p><?= e($erro) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= csrf_campo() ?>

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" required>

        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" autocomplete="current-password" required>

        <button class="btn" type="submit">Entrar</button>

        <p class="auth-extra">Ainda não tem conta? <a href="/cadastro.php">Cadastre-se</a></p>
    </form>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
