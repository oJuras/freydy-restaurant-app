<?php
/**
 * API para Atualizar Status de Pedidos
 * Freydy Restaurant App
 */

header('Content-Type: application/json');

require_once '../../includes/auth.php';
require_once '../../models/Pedido.php';

// Verifica se usuário está logado
$auth->requerLogin();

// Verifica se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Recebe dados JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['pedido_id']) || !isset($input['status'])) {
        throw new Exception('Dados incompletos');
    }
    
    $pedidoId = (int) $input['pedido_id'];
    $novoStatus = $input['status'];
    $usuarioId = $auth->getUsuarioId();
    
    // Valida status
    $statusValidos = ['pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado'];
    if (!in_array($novoStatus, $statusValidos)) {
        throw new Exception('Status inválido');
    }
    
    // Atualiza status do pedido
    $pedidoModel = new Pedido();
    $resultado = $pedidoModel->atualizarStatus($pedidoId, $novoStatus, $usuarioId);
    
    if ($resultado) {
        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso',
            'pedido_id' => $pedidoId,
            'novo_status' => $novoStatus
        ]);
    } else {
        throw new Exception('Erro ao atualizar status');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
