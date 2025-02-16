<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['restaurantId'])) {
    header("Location: login.php");
    exit();
}

require_once "./src/Store.php";

$databaseDirectory = __DIR__ . "/dadosPedidos";
$pedidosStore = new \SleekDB\Store("pedidos", $databaseDirectory);

// Obtém o pedido enviado via POST (JSON)
$pedidoJson = file_get_contents('php://input');
$pedido = json_decode($pedidoJson, true);

// Verifica se os dados estão completos
if (isset($pedido['Mesa']) && isset($pedido['Itens']) && isset($pedido['Status'])) {
    // Salva o pedido no banco de dados
    $results = $pedidosStore->insert($pedido);
    
    // Responde com sucesso
    echo json_encode(['success' => true, 'message' => 'Pedido salvo com sucesso!']);
} else {
    // Se os dados não estiverem completos, retorna um erro
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
}
?>
