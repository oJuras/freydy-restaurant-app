<?php
require_once '../../includes/auth.php';
require_once '../../models/Pedido.php';

$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $pedidoId = $_GET['id'] ?? null;
    
    if (!$pedidoId) {
        throw new Exception('ID do pedido é obrigatório');
    }
    
    $pedidoModel = new Pedido();
    $pedido = $pedidoModel->buscarPorIdComRestaurante($pedidoId, $usuario['restaurante_id']);
    
    if (!$pedido) {
        throw new Exception('Pedido não encontrado');
    }
    
    // Formatar dados para exibição
    $pedido['data_pedido_formatada'] = date('d/m/Y H:i', strtotime($pedido['data_pedido']));
    $pedido['valor_total_formatado'] = 'R$ ' . number_format($pedido['valor_total'], 2, ',', '.');
    
    // Formatar itens
    foreach ($pedido['itens'] as &$item) {
        $item['preco_unitario_formatado'] = 'R$ ' . number_format($item['preco_unitario'], 2, ',', '.');
        $item['subtotal'] = $item['quantidade'] * $item['preco_unitario'];
        $item['subtotal_formatado'] = 'R$ ' . number_format($item['subtotal'], 2, ',', '.');
    }
    
    // Formatar histórico
    foreach ($pedido['historico'] as &$historico) {
        $historico['data_formatada'] = date('d/m/Y H:i', strtotime($historico['data_alteracao']));
        $historico['status_anterior_formatado'] = $historico['status_anterior'] ? ucfirst(str_replace('_', ' ', $historico['status_anterior'])) : 'Criação';
        $historico['status_novo_formatado'] = ucfirst(str_replace('_', ' ', $historico['status_novo']));
    }
    
    echo json_encode([
        'success' => true,
        'pedido' => $pedido
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
