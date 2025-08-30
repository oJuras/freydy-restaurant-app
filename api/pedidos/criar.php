<?php
require_once '../../includes/auth.php';
require_once '../../models/Pedido.php';
require_once '../../models/Mesa.php';
require_once '../../models/Produto.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['mesa_id'], $input['itens']) || !is_array($input['itens']) || count($input['itens']) == 0) {
        throw new Exception('Selecione a mesa e ao menos um produto');
    }
    $mesaId = (int)$input['mesa_id'];
    $observacao = trim($input['observacao'] ?? '');
    $pedidoItens = $input['itens'];
    $pedidoModel = new Pedido();
    $mesaModel = new Mesa();
    $produtoModel = new Produto();
    // Valida mesa
    $mesa = $mesaModel->buscarPorId($mesaId);
    if (!$mesa || $mesa['restaurante_id'] != $usuario['restaurante_id']) throw new Exception('Mesa inválida');
    // Valida e calcula itens
    $valorTotal = 0;
    $itensParaSalvar = [];
    foreach ($pedidoItens as $item) {
        $produto = $produtoModel->buscarPorId($item['produto_id']);
        if (!$produto || $produto['restaurante_id'] != $usuario['restaurante_id'] || $produto['status'] != 'ativo') {
            throw new Exception('Produto inválido');
        }
        $qtd = max(1, (int)$item['quantidade']);
        $valorTotal += $produto['preco'] * $qtd;
        $itensParaSalvar[] = [
            'produto_id' => $produto['id'],
            'quantidade' => $qtd,
            'preco_unitario' => $produto['preco']
        ];
    }
    // Cria pedido
    $dadosPedido = [
        'restaurante_id' => $usuario['restaurante_id'],
        'mesa_id' => $mesaId,
        'usuario_id' => $usuario['id'],
        'valor_total' => $valorTotal,
        'observacao' => $observacao,
        'status' => 'pendente'
    ];
    $pedidoId = $pedidoModel->criarComItens($dadosPedido, $itensParaSalvar);
    $pedido = $pedidoModel->buscarPorId($pedidoId);
    echo json_encode(['success' => true, 'pedido' => $pedido]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
