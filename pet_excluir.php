<?php
require __DIR__ . '/includes/funcoes.php';
require __DIR__ . '/config/conexao.php';

exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('/pets.php');
}
csrf_validar();

$u  = usuario_atual();
$id = (int) ($_POST['id'] ?? 0);

// O "AND usuario_id" impede de apagar o pet de outra pessoa.
$remover = $pdo->prepare('DELETE FROM pets WHERE id = :id AND usuario_id = :u');
$remover->execute([':id' => $id, ':u' => $u['id']]);

flash('Pet removido.');
redirecionar('/pets.php');
