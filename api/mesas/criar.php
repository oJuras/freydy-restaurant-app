<?php
/**
 * API para Criar Mesa
 * Freydy Restaurant App
 */

header('Content-Type: application/json');

require_once '../../includes/auth.php';
require_once '../../models/Mesa.php';

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
    
    if (!isset($input['numero'])) {
        throw new Exception('Número da mesa é obrigatório');
    }
    
    $usuario = $auth->getUsuario();
    
    $dados = [
        'restaurante_id' => $usuario['restaurante_id'],
        'numero' => (int) $input['numero'],
        'capacidade' => (int) ($input['capacidade'] ?? 4),
        'posicao_x' => (int) ($input['posicao_x'] ?? 0),
        'posicao_y' => (int) ($input['posicao_y'] ?? 0),
        'status' => $input['status'] ?? 'livre'
    ];
    
    // Validações
    if ($dados['numero'] <= 0) {
        throw new Exception('Número da mesa deve ser maior que zero');
    }
    
    if ($dados['capacidade'] <= 0) {
        throw new Exception('Capacidade deve ser maior que zero');
    }
    
    if ($dados['capacidade'] > 20) {
        throw new Exception('Capacidade máxima é 20 pessoas');
    }
    
    $mesaModel = new Mesa();
    
    // Verifica se já existe mesa com este número
    $mesaExistente = $mesaModel->buscarPorNumero($dados['numero'], $dados['restaurante_id']);
    if ($mesaExistente) {
        throw new Exception('Já existe uma mesa com este número');
    }
    
    // Cria a mesa
    $mesaId = $mesaModel->criar($dados);
    
    // Busca a mesa criada
    $mesa = $mesaModel->buscarPorId($mesaId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Mesa criada com sucesso',
        'mesa' => $mesa
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
