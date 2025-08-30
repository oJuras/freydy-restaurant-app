<?php
/**
 * API para Criar Categoria
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
    
    if (!isset($input['nome'])) {
        throw new Exception('Nome da categoria é obrigatório');
    }
    
    $usuario = $auth->getUsuario();
    
    $dados = [
        'restaurante_id' => $usuario['restaurante_id'],
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
    
    // Verifica se já existe categoria com este nome
    $categoriaModel = new Categoria();
    $categoriaExistente = $categoriaModel->buscarPorNome($dados['nome'], $dados['restaurante_id']);
    
    if ($categoriaExistente) {
        throw new Exception('Já existe uma categoria com este nome');
    }
    
    // Cria a categoria
    $categoriaId = $categoriaModel->criar($dados);
    
    // Busca a categoria criada
    $categoria = $categoriaModel->buscarPorId($categoriaId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Categoria criada com sucesso',
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
