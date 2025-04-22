<?php
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->logout();

// Redirigir a la página de inicio
header("Location: /");
exit;
?>