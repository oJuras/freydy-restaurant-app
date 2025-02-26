<?php
session_start();

if (!isset($_SESSION['restaurantId'])) {
    header("Location: login.php");
    exit();
}

require_once "./src/Store.php";

$databaseDirectory = __DIR__ . "/dadosPedidos";
$pedidosStore = new \SleekDB\Store("pedidos", $databaseDirectory);

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id']) && isset($data['status'])) {
    $pedidosStore->updateById($data['id'], ['Status' => $data['status']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}
?>