<?php
require_once '../../includes/auth.php';
require_once '../../models/Pedido.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit();
}

try {
    $pedidoModel = new Pedido();
    $pedido = $pedidoModel->buscarPorId($_GET['id']);
    if (!$pedido || $pedido['restaurante_id'] != $usuario['restaurante_id']) {
        throw new Exception('Pedido não encontrado');
    }
    $historico = $pedidoModel->buscarHistorico($pedido['id']);
    echo json_encode([
        'success' => true,
        'pedido' => $pedido,
        'historico' => $historico
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
