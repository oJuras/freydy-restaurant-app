<?php
/**
 * Página de Logout
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Faz logout do usuário
$auth->logout();

// Redireciona para página de login
header("Location: login.php");
exit();
?>
