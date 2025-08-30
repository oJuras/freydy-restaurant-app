<?php
/**
 * API para Atualizar Categoria
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
    
    if (!isset($input['id']) || !isset($input['nome'])) {
        throw new Exception('ID e nome da categoria são obrigatórios');
    }
    
    $usuario = $auth->getUsuario();
    $categoriaId = (int) $input['id'];
    
    $dados = [
        'nome' => trim($input['nome']),
        'descricao' => trim($input['descricao'] ?? ''),
        'status' => $input['status'] ?? 'ativo'
    ];
    
    // Validações
    if (empty($dados['nome'])) {
        throw new Exception('Nome da categoria não pode estar vazio');
    }
    
    if (strlen($dados['nome']) > 50) {
        throw new Exception('Nome da categoria deve ter no máximo 50 caracteres');
    }
    
    $categoriaModel = new Categoria();
    
    // Verifica se a categoria existe e pertence ao restaurante
    $categoriaExistente = $categoriaModel->buscarPorId($categoriaId);
    if (!$categoriaExistente) {
        throw new Exception('Categoria não encontrada');
    }
    
    if (!$categoriaModel->pertenceAoRestaurante($categoriaId, $usuario['restaurante_id'])) {
        throw new Exception('Categoria não pertence ao seu restaurante');
    }
    
    // Verifica se já existe outra categoria com este nome
    $categoriaComMesmoNome = $categoriaModel->buscarPorNome($dados['nome'], $usuario['restaurante_id']);
    if ($categoriaComMesmoNome && $categoriaComMesmoNome['id'] != $categoriaId) {
        throw new Exception('Já existe uma categoria com este nome');
    }
    
    // Atualiza a categoria
    $categoriaModel->atualizar($categoriaId, $dados);
    
    // Busca a categoria atualizada
    $categoria = $categoriaModel->buscarPorId($categoriaId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoria atualizada com sucesso',
        'categoria' => $categoria
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
