<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function logado(): bool
{
    return isset($_SESSION['usuario']);
}

function usuario_atual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigir_login(): void
{
    if (!logado()) {
        flash('Faça login para continuar.', 'erro');
        redirecionar('/login.php');
    }
}

function exigir_admin(): void
{
    exigir_login();
    if (empty($_SESSION['usuario']['e_admin'])) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}


function flash(string $mensagem, string $tipo = 'ok'): void
{
    $_SESSION['flash'] = ['msg' => $mensagem, 'tipo' => $tipo];
}

function pegar_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_validar(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Requisição inválida. Volte e tente novamente.');
    }
}

function icone_servico(string $nome): string
{
    $n = strtolower($nome);
    if (strpos($n, 'banho') !== false) { return '🛁'; }
    if (strpos($n, 'tosa') !== false) { return '✂️'; }
    if (strpos($n, 'consulta') !== false) { return '🩺'; }
    if (strpos($n, 'vacin') !== false) { return '💉'; }
    return '🐾';
}

function data_br(?string $data, bool $com_hora = false): string
{
    if (!$data) {
        return '-';
    }
    return date($com_hora ? 'd/m/Y H:i' : 'd/m/Y', strtotime($data));
}

function preco_br($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}
