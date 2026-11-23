<?php
require __DIR__. '/../includes/funcoes.php';
require __DIR__. '/../config/conexao.php';

exigir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('/admin/produtos.php');
}
csrf_validar();

$id = (int) ($_POST['id'] ?? 0);

try {
    $pdo->prepare('DELETE FROM produtos WHERE id = :id')->execute([':id' => $id]);
    flash('Produto excluído.');
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    flash('Não foi possível excluir o produto.', 'erro');
}

redirecionar('/admin/produtos.php');