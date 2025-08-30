<?php
require_once '../../includes/auth.php';
require_once '../../models/Usuario.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['id'])) throw new Exception('ID do usuário é obrigatório');
    $usuarioModel = new Usuario();
    $usuarioExistente = $usuarioModel->buscarPorId($input['id']);
    if (!$usuarioExistente) throw new Exception('Usuário não encontrado');
    if ($usuarioExistente['restaurante_id'] != $usuario['restaurante_id']) throw new Exception('Usuário não pertence ao restaurante');
    $usuarioModel->atualizar($input['id'], [
        'nome' => $usuarioExistente['nome'],
        'email' => $usuarioExistente['email'],
        'tipo_usuario' => $usuarioExistente['tipo_usuario'],
        'status' => 'inativo'
    ]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
