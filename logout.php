<?php
require __DIR__ . '/includes/funcoes.php';

$_SESSION = [];
session_destroy();

redirecionar('/index.php');
