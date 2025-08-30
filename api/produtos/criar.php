<?php
/**
 * API para Criar Produto
 * Freydy Restaurant App
 */

header('Content-Type: application/json');

require_once '../../includes/auth.php';
require_once '../../models/Produto.php';
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
    
    if (!isset($input['nome']) || !isset($input['categoria_id']) || !isset($input['preco'])) {
        throw new Exception('Nome, categoria e preço são obrigatórios');
    }
    
    $usuario = $auth->getUsuario();
    
    $dados = [
        'restaurante_id' => $usuario['restaurante_id'],
        'categoria_id' => (int) $input['categoria_id'],
        'nome' => trim($input['nome']),
        'descricao' => trim($input['descricao'] ?? ''),
        'preco' => (float) $input['preco'],
        'tempo_preparo' => (int) ($input['tempo_preparo'] ?? 15),
        'imagem_url' => trim($input['imagem_url'] ?? ''),
        'status' => $input['status'] ?? 'ativo'
    ];
    
    // Validações
    if (empty($dados['nome'])) {
        throw new Exception('Nome do produto não pode estar vazio');
    }
    
    if (strlen($dados['nome']) > 100) {
        throw new Exception('Nome do produto deve ter no máximo 100 caracteres');
    }
    
    if ($dados['preco'] <= 0) {
        throw new Exception('Preço deve ser maior que zero');
    }
    
    if ($dados['tempo_preparo'] <= 0) {
        throw new Exception('Tempo de preparo deve ser maior que zero');
    }
    
    // Verifica se a categoria existe e pertence ao restaurante
    $categoriaModel = new Categoria();
    $categoria = $categoriaModel->buscarPorId($dados['categoria_id']);
    if (!$categoria) {
        throw new Exception('Categoria não encontrada');
    }
    
    if (!$categoriaModel->pertenceAoRestaurante($dados['categoria_id'], $dados['restaurante_id'])) {
        throw new Exception('Categoria não pertence ao seu restaurante');
    }
    
    // Cria o produto
    $produtoModel = new Produto();
    $produtoId = $produtoModel->criar($dados);
    
    // Busca o produto criado
    $produto = $produtoModel->buscarPorId($produtoId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Produto criado com sucesso',
        'produto' => $produto
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
