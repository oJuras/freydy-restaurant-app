<?php
/**
 * API para Excluir Categoria
 * Freydy Restaurant App
 */

header('Content-Type: application/json');

require_once '../../includes/auth.php';
require_once '../../models/Categoria.php';

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
        throw new Exception('ID da categoria é obrigatório');
    }
    
    $usuario = $auth->getUsuario();
    $categoriaId = (int) $input['id'];
    
    $categoriaModel = new Categoria();
    
    // Verifica se a categoria existe e pertence ao restaurante
    $categoriaExistente = $categoriaModel->buscarPorId($categoriaId);
    if (!$categoriaExistente) {
        throw new Exception('Categoria não encontrada');
    }
    
    if (!$categoriaModel->pertenceAoRestaurante($categoriaId, $usuario['restaurante_id'])) {
        throw new Exception('Categoria não pertence ao seu restaurante');
    }
    
    // Verifica se há produtos nesta categoria
    $produtosCategoria = $categoriaModel->contarProdutos($categoriaId);
    if ($produtosCategoria > 0) {
        throw new Exception("Não é possível excluir uma categoria que possui {$produtosCategoria} produto(s)");
    }
    
    // Exclui a categoria
    $categoriaModel->excluir($categoriaId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoria excluída com sucesso',
        'categoria_id' => $categoriaId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
