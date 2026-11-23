<?php
require_once __DIR__ . '/funcoes.php';
$u = usuario_atual();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($titulo ?? 'Início') . ' | Clici Pets Shopping') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/asset/css/style.css">
</head>
<body>
<header class="site-header">
    <a href="/index.php" class="logo">
        <span class="logo-icon">🐾</span>
        <span class="logo-text">Clici<small>Pets Shopping</small></span>
    </a>
    <input type="checkbox" id="menu-toggle" class="menu-toggle">
    <label for="menu-toggle" class="menu-btn" aria-label="Abrir menu">☰</label>
    <nav class="menu">
        <a href="/index.php">Início</a>
        <?php if ($u): ?>
            <a href="/pets.php">Meus pets</a>
            <a href="/meus_agendamentos.php">Agendamentos</a>
            <?php if (!empty($u['e_admin'])): ?>
                <a href="/admin/index.php">Admin</a>
            <?php endif; ?>
            <a href="/logout.php" class="menu-sair">Sair (<?= e(explode(' ', $u['nome'])[0]) ?>)</a>
        <?php else: ?>
            <a href="/login.php">Entrar</a>
            <a href="/cadastro.php" class="menu-cta">Criar conta</a>
        <?php endif; ?>
    </nav>
</header>
<?php if ($f = pegar_flash()): ?>
    <main class="main-flash"><div class="msg <?= e($f['tipo']) ?>"><?= e($f['msg']) ?></div></main>
<?php endif; ?>
