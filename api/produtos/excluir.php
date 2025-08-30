<?php
/**
 * API para Excluir Produto
 * Freydy Restaurant App
 */

header('Content-Type: application/json');

require_once '../../includes/auth.php';
require_once '../../models/Produto.php';

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
    
    if (!isset($input['id'])) {
        throw new Exception('ID do produto é obrigatório');
    }
    
    $usuario = $auth->getUsuario();
    $produtoId = (int) $input['id'];
    
    $produtoModel = new Produto();
    
    // Verifica se o produto existe
    $produtoExistente = $produtoModel->buscarPorId($produtoId);
    if (!$produtoExistente) {
        throw new Exception('Produto não encontrado');
    }
    
    // Verifica se o produto pertence ao restaurante
    if ($produtoExistente['restaurante_id'] != $usuario['restaurante_id']) {
        throw new Exception('Produto não pertence ao seu restaurante');
    }
    
    // Remove o produto (soft delete)
    $produtoModel->remover($produtoId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Produto excluído com sucesso',
        'produto_id' => $produtoId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
