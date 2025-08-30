<?php
require_once '../../includes/auth.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();
require_once '../../models/Usuario.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $senhaAtual = $_POST['senha_atual'] ?? '';
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';
    if (!$senhaAtual || !$novaSenha || !$confirmar) throw new Exception('Preencha todos os campos');
    if ($novaSenha !== $confirmar) throw new Exception('Nova senha e confirmação não conferem');
    if (strlen($novaSenha) < 6) throw new Exception('A nova senha deve ter pelo menos 6 caracteres');
    $usuarioModel = new Usuario();
    $u = $usuarioModel->buscarPorId($usuario['id']);
    if (!$u || !password_verify($senhaAtual, $u['senha'])) throw new Exception('Senha atual incorreta');
    $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $usuarioModel->db->query($sql, [$hash, $usuario['id']]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
