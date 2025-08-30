<?php
require_once '../../includes/auth.php';
require_once '../../models/Usuario.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

try {
    $usuarioModel = new Usuario();
    $usuarios = $usuarioModel->listarPorRestaurante($usuario['restaurante_id']);
    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
