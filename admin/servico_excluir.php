<?php
require __DIR__ . '/../includes/funcoes.php';
require __DIR__ . '/../config/conexao.php';

exigir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('/admin/servicos.php');
}
csrf_validar();

$id = (int) ($_POST['id'] ?? 0);

try {
    $pdo->prepare('DELETE FROM servicos WHERE id = :id')->execute([':id' => $id]);
    flash('Serviço excluído.');
} catch (PDOException $ex) {
    // Código 23000: o serviço já tem agendamentos ligados a ele.
    if ($ex->getCode() === '23000') {
        flash('Este serviço já tem agendamentos. Em vez de excluir, desmarque "ativo" ao editar.', 'erro');
    } else {
        error_log($ex->getMessage());
        flash('Não foi possível excluir o serviço.', 'erro');
    }
}

redirecionar('/admin/servicos.php');
