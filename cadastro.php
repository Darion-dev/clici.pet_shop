<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

if (logado()) {
    redirecionar('/index.php');
}

$erros = [];
$nome  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validar();

    $nome           = trim($_POST['nome'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $senha          = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';

    if ($nome === '') {
        $erros[] = 'Informe seu nome.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (strlen($senha) < 8) {
        $erros[] = 'A senha deve ter pelo menos 8 caracteres.';
    }
    if ($senha !== $confirmarSenha) {
        $erros[] = 'As senhas não são iguais.';
    }

    if (!$erros) {
        try {
            $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
            $consulta->execute([':email' => $email]);

            if ($consulta->fetch()) {
                $erros[] = 'Já existe uma conta com esse e-mail.';
            } else {
                // Nunca guarde a senha original: use sempre password_hash.
                $inserir = $pdo->prepare(
                    'INSERT INTO usuarios (nome, email, senha, e_admin)
                     VALUES (:nome, :email, :senha, 0)'
                );
                $inserir->execute([
                    ':nome'  => $nome,
                    ':email' => $email,
                    ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                ]);

                flash('Conta criada com sucesso! Agora é só entrar.');
                redirecionar('/login.php');
            }
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $erros[] = 'Não foi possível concluir o cadastro. Tente novamente mais tarde.';
        }
    }
}

$titulo = 'Criar conta';
require __DIR__ . '/includes/cabecalho.php';
?>
<main>
    <form class="box centro" action="/cadastro.php" method="post" novalidate>
        <h2>Crie sua conta</h2>
        <p class="sub">Cadastre-se para agendar serviços para o seu pet.</p>

        <?php if ($erros): ?>
            <div class="msg erro" role="alert">
                <?php foreach ($erros as $erro): ?>
                    <p><?= e($erro) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= csrf_campo() ?>

        <label for="nome">Nome completo</label>
        <input id="nome" name="nome" type="text" value="<?= e($nome) ?>" required>

        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" value="<?= e($email) ?>" required>

        <label for="senha">Senha (mínimo 8 caracteres)</label>
        <input id="senha" name="senha" type="password" minlength="8" autocomplete="new-password" required>

        <label for="confirmar_senha">Confirmar senha</label>
        <input id="confirmar_senha" name="confirmar_senha" type="password" minlength="8" autocomplete="new-password" required>

        <button class="btn" type="submit">Criar minha conta</button>

        <p class="auth-extra">Já tem conta? <a href="/login.php">Entrar</a></p>
    </form>
</main>
<?php require __DIR__ . '/includes/rodape.php'; ?>
