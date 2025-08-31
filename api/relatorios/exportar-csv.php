<?php
require_once '../../includes/auth.php';
require_once '../../models/Pedido.php';
require_once '../../models/Produto.php';
require_once '../../models/Mesa.php';

$auth->requerLogin();
$usuario = $auth->getUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $tipo = $_GET['tipo'] ?? 'pedidos';
    $inicio = $_GET['inicio'] ?? null;
    $fim = $_GET['fim'] ?? null;
    $restauranteId = $usuario['restaurante_id'];

    $pedidoModel = new Pedido();
    $produtoModel = new Produto();
    $mesaModel = new Mesa();

    // Configurar headers para download
    $filename = "relatorio_{$tipo}_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

    // Criar arquivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    switch ($tipo) {
        case 'pedidos':
            exportarPedidos($output, $pedidoModel, $restauranteId, $inicio, $fim);
            break;
        case 'produtos':
            exportarProdutos($output, $produtoModel, $restauranteId);
            break;
        case 'mesas':
            exportarMesas($output, $mesaModel, $restauranteId);
            break;
        default:
            throw new Exception('Tipo de relatório inválido');
    }

    fclose($output);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function exportarPedidos($output, $pedidoModel, $restauranteId, $inicio, $fim) {
    // Cabeçalho
    fputcsv($output, [
        'Número do Pedido',
        'Mesa',
        'Status',
        'Valor Total',
        'Data do Pedido',
        'Observações'
    ]);

    // Buscar pedidos
    $sql = "SELECT p.*, m.numero as mesa_numero 
            FROM pedidos p 
            LEFT JOIN mesas m ON p.mesa_id = m.id 
            WHERE p.restaurante_id = ?";
    $params = [$restauranteId];
    
    if ($inicio) {
        $sql .= " AND p.data_pedido >= ?";
        $params[] = $inicio;
    }
    if ($fim) {
        $sql .= " AND p.data_pedido <= ?";
        $params[] = $fim;
    }
    
    $sql .= " ORDER BY p.data_pedido DESC";
    
    $pedidos = $pedidoModel->db->fetchAll($sql, $params);
    
    foreach ($pedidos as $pedido) {
        fputcsv($output, [
            $pedido['numero_pedido'],
            $pedido['mesa_numero'] ?? 'N/A',
            ucfirst(str_replace('_', ' ', $pedido['status'])),
            'R$ ' . number_format($pedido['valor_total'], 2, ',', '.'),
            date('d/m/Y H:i', strtotime($pedido['data_pedido'])),
            $pedido['observacoes'] ?? ''
        ]);
    }
}

function exportarProdutos($output, $produtoModel, $restauranteId) {
    // Cabeçalho
    fputcsv($output, [
        'Nome',
        'Categoria',
        'Preço',
        'Tempo de Preparo',
        'Status',
        'Descrição'
    ]);

    // Buscar produtos
    $sql = "SELECT p.*, c.nome as categoria_nome 
            FROM produtos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.restaurante_id = ? 
            ORDER BY p.nome";
    
    $produtos = $produtoModel->db->fetchAll($sql, [$restauranteId]);
    
    foreach ($produtos as $produto) {
        fputcsv($output, [
            $produto['nome'],
            $produto['categoria_nome'] ?? 'Sem categoria',
            'R$ ' . number_format($produto['preco'], 2, ',', '.'),
            $produto['tempo_preparo'] . ' min',
            ucfirst($produto['status']),
            $produto['descricao'] ?? ''
        ]);
    }
}

function exportarMesas($output, $mesaModel, $restauranteId) {
    // Cabeçalho
    fputcsv($output, [
        'Número',
        'Capacidade',
        'Status',
        'Posição X',
        'Posição Y'
    ]);

    // Buscar mesas
    $mesas = $mesaModel->listarPorRestaurante($restauranteId);
    
    foreach ($mesas as $mesa) {
        fputcsv($output, [
            $mesa['numero'],
            $mesa['capacidade'] . ' pessoas',
            ucfirst($mesa['status']),
            $mesa['posicao_x'] ?? 0,
            $mesa['posicao_y'] ?? 0
        ]);
    }
}
