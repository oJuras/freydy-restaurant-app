<?php
session_start();

if (!isset($_SESSION['restaurantId'])) {
    header("Location: login.php");
    exit();
}

require_once "./src/Store.php";

$databaseDirectory = __DIR__ . "/dadosPedidos";
$pedidosStore = new \SleekDB\Store("pedidos", $databaseDirectory);

$pedidoJson = file_get_contents('php://input');
$pedido = json_decode($pedidoJson, true);

if (isset($pedido['Mesa']) && isset($pedido['Itens']) && isset($pedido['Status'])) {
    $results = $pedidosStore->insert($pedido);
    
    echo json_encode(['success' => true, 'message' => 'Pedido salvo com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}
?>
