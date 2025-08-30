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
    if (!isset($input['id'], $input['nome'], $input['email'], $input['tipo_usuario'], $input['status'])) {
        throw new Exception('Preencha todos os campos obrigatórios');
    }
    $usuarioModel = new Usuario();
    $usuarioExistente = $usuarioModel->buscarPorId($input['id']);
    if (!$usuarioExistente) throw new Exception('Usuário não encontrado');
    if ($usuarioExistente['restaurante_id'] != $usuario['restaurante_id']) throw new Exception('Usuário não pertence ao restaurante');
    $usuarioModel->atualizar($input['id'], [
        'nome' => trim($input['nome']),
        'email' => trim($input['email']),
        'tipo_usuario' => $input['tipo_usuario'],
        'status' => $input['status']
    ]);
    $usuarioAtualizado = $usuarioModel->buscarPorId($input['id']);
    echo json_encode(['success' => true, 'usuario' => $usuarioAtualizado]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
