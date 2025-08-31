<?php
require_once '../../includes/auth.php';
require_once '../../models/Reserva.php';

$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validações
    if (empty($input['id'])) {
        throw new Exception('ID da reserva é obrigatório');
    }
    
    $reservaModel = new Reserva();
    $reservaModel->excluir($input['id'], $usuario['restaurante_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reserva excluída com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
