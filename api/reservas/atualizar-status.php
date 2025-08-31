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
    
    if (empty($input['status'])) {
        throw new Exception('Status é obrigatório');
    }
    
    // Validar status
    $statusPermitidos = ['pendente', 'confirmada', 'cancelada', 'concluida'];
    if (!in_array($input['status'], $statusPermitidos)) {
        throw new Exception('Status inválido');
    }
    
    $reservaModel = new Reserva();
    $reservaModel->atualizarStatus($input['id'], $input['status'], $usuario['restaurante_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Status da reserva atualizado com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
