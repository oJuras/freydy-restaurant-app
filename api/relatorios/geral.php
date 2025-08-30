<?php
require_once '../../includes/auth.php';
require_once '../../models/Pedido.php';
require_once '../../models/Produto.php';
require_once '../../models/Mesa.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

$inicio = isset($_GET['inicio']) && $_GET['inicio'] ? $_GET['inicio'] : null;
$fim = isset($_GET['fim']) && $_GET['fim'] ? $_GET['fim'] : null;

try {
    $pedidoModel = new Pedido();
    $produtoModel = new Produto();
    $mesaModel = new Mesa();
    $restauranteId = $usuario['restaurante_id'];

    // Resumo
    $resumo = $pedidoModel->calcularEstatisticas($restauranteId, $inicio, $fim);
    if (!$resumo) $resumo = [
        'total_pedidos' => 0,
        'valor_total' => 0,
        'valor_medio' => 0,
        'pedidos_entregues' => 0,
        'pedidos_cancelados' => 0
    ];

    // Pedidos por status
    $statusLabels = ['pendente','em_preparo','pronto','entregue','cancelado'];
    $pedidosStatus = array_fill_keys($statusLabels, 0);
    $sql = "SELECT status, COUNT(*) as total FROM pedidos WHERE restaurante_id = ?";
    $params = [$restauranteId];
    if ($inicio) { $sql .= " AND data_pedido >= ?"; $params[] = $inicio; }
    if ($fim) { $sql .= " AND data_pedido <= ?"; $params[] = $fim; }
    $sql .= " GROUP BY status";
    $db = $pedidoModel->db;
    $result = $db->fetchAll($sql, $params);
    foreach ($result as $row) {
        $pedidosStatus[$row['status']] = (int)$row['total'];
    }

    // Produtos mais vendidos
    $sql = "SELECT p.nome, SUM(ip.quantidade) as total FROM itens_pedido ip INNER JOIN produtos p ON ip.produto_id = p.id INNER JOIN pedidos pd ON ip.pedido_id = pd.id WHERE pd.restaurante_id = ?";
    $params = [$restauranteId];
    if ($inicio) { $sql .= " AND pd.data_pedido >= ?"; $params[] = $inicio; }
    if ($fim) { $sql .= " AND pd.data_pedido <= ?"; $params[] = $fim; }
    $sql .= " GROUP BY p.nome ORDER BY total DESC LIMIT 7";
    $maisVendidos = $db->fetchAll($sql, $params);
    $prodLabels = []; $prodData = [];
    foreach ($maisVendidos as $row) {
        $prodLabels[] = $row['nome'];
        $prodData[] = (int)$row['total'];
    }

    // Ocupação de mesas
    $mesas = $mesaModel->listarPorRestaurante($restauranteId);
    $ocupadas = 0; $livres = 0; $reservadas = 0; $manutencao = 0;
    foreach ($mesas as $m) {
        if ($m['status'] == 'ocupada') $ocupadas++;
        elseif ($m['status'] == 'livre') $livres++;
        elseif ($m['status'] == 'reservada') $reservadas++;
        elseif ($m['status'] == 'manutencao') $manutencao++;
    }

    echo json_encode([
        'success' => true,
        'resumo' => $resumo,
        'pedidos_status' => [
            'labels' => ['Pendente','Em Preparo','Pronto','Entregue','Cancelado'],
            'data' => array_values($pedidosStatus)
        ],
        'produtos_mais_vendidos' => [
            'labels' => $prodLabels,
            'data' => $prodData
        ],
        'mesas_ocupadas' => [
            'labels' => ['Ocupadas','Livres','Reservadas','Manutenção'],
            'data' => [$ocupadas, $livres, $reservadas, $manutencao]
        ]
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
