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
    if (!isset($input['nome'], $input['email'], $input['senha'], $input['tipo_usuario'])) {
        throw new Exception('Preencha todos os campos obrigatórios');
    }
    $dados = [
        'restaurante_id' => $usuario['restaurante_id'],
        'nome' => trim($input['nome']),
        'email' => trim($input['email']),
        'senha' => $input['senha'],
        'tipo_usuario' => $input['tipo_usuario']
    ];
    $usuarioModel = new Usuario();
    // Verifica se já existe email
    $existe = $usuarioModel->buscarPorEmail($dados['email'], $usuario['restaurante_id']);
    if ($existe) throw new Exception('Já existe um usuário com este e-mail');
    $id = $usuarioModel->criar($dados);
    $novoUsuario = $usuarioModel->buscarPorId($id);
    echo json_encode(['success' => true, 'usuario' => $novoUsuario]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
