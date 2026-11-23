<?php

$host    = 'localhost';
$banco   = 'pet_shop';
$usuario = 'root';
$senha   = 'root';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Erro ao conectar ao banco: ' . htmlspecialchars($e->getMessage()));
}
