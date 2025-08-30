<?php
require_once '../../includes/auth.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $db = Database::getInstance();
    $id = $usuario['restaurante_id'];
    $nome = trim($_POST['nome_restaurante'] ?? '');
    $email = trim($_POST['email_restaurante'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    if (!$nome) throw new Exception('Nome é obrigatório');
    $sql = "UPDATE restaurantes SET nome = ?, email = ?, telefone = ?, endereco = ? WHERE id = ?";
    $db->query($sql, [$nome, $email, $telefone, $endereco, $id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
