<?php
session_start();
require_once "./src/Store.php";
$databaseDirectory = __DIR__ . "/dadosPedidos";
$pedidosStore = new \SleekDB\Store("pedidos", $databaseDirectory);

if (!isset($_SESSION['restaurantId'])) {
    header("Location: login.php");
    exit();
}

$pedidosAndamento = $pedidosStore->findBy(["Status", "==", "Em Andamento"]);

$pedidosConcluidos = $pedidosStore->findBy(["Status", "==", "Concluído"]);

$restaurantId = $_SESSION['restaurantId'];
$password = $_SESSION['password'];

function gerarNumeroAleatorio() {
    return rand(1, 100); 
}

function gerarDescricaoPedido() {
    $descricoes = [
        "Pedido com diversos itens de comida e bebidas.",
        "Mesa com aperitivos e prato principal.",
        "Pedido de entrada, prato principal e sobremesa.",
        "Pedido variado com bebidas e entradas.",
        "Pedido com especialidades da casa.",
        "Mesa com prato vegetariano e sucos naturais.",
    ];
    return $descricoes[array_rand($descricoes)];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="./css/styles.css">
    <script src="./js/scripts.js"></script>
</head>
<body>

<div class="tabs">
    <div id="tabAndamento" class="active" onclick="changeTab('andamento')">Pedidos em Andamento</div>
    <div id="tabConcluidos" onclick="changeTab('concluidos')">Pedidos Concluídos</div>
    <div id="tabFiltros" onclick="changeTab('filtros')">Filtros</div>
</div>

<div class="container">
    <div id="andamento" class="content active">
        <h1>Pedidos em Andamento</h1>
        <ol id="pedidoEmAndamentoList">
            <?php foreach ($pedidosAndamento as $pedido): ?>
                <li class="order-item">
                    <span class="order-id">Mesa #<?php echo $pedido['Mesa']; ?></span>
                    <span class="order-description"><?php echo implode(", ", $pedido['Itens']); ?></span>
                    <button class="button green" onclick="finalizarPedido(this, '<?php echo $pedido['_id']; ?>')">Finalizar</button>
                    <button class="button print" onclick="imprimirComanda(this)">Imprimir Comanda</button>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>

    <div id="concluidos" class="content">
        <h1>Pedidos Concluídos</h1>
        <ol id="pedidoConcluidoList">
            <?php foreach ($pedidosConcluidos as $pedido): ?>
                <li class="order-item">
                    <span class="order-id">Mesa #<?php echo $pedido['Mesa']; ?></span>
                    <span class="order-description"><?php echo implode(", ", $pedido['Itens']); ?></span>
                    <button class="button red" onclick="retornarPedido(this, '<?php echo $pedido['_id']; ?>')">Retornar para Em Andamento</button>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>

    <div id="filtros" class="content">
        <h1>Filtros</h1>
        <div class="filters">
            <label for="mesa">Filtrar por Mesa:</label>
            <input type="number" id="mesa" placeholder="Número da Mesa">
            <br>
            <label for="status">Filtrar por Status:</label>
            <select id="status">
                <option value="">Selecione o Status</option>
                <option value="andamento">Em andamento</option>
                <option value="concluido">Concluído</option>
            </select>
            <button class="button green" onclick="filtrarPedidos()">Aplicar Filtros</button>
        </div>

        <h2>Pedidos Filtrados</h2>
        <ol id="filtroResultados">
        </ol>
    </div>

    <div class="approval">
        <h2>Aprovação</h2>
        <ol>
            <li class="service-item">
                <span>Mesa <?php echo gerarNumeroAleatorio(); ?></span>
                <div>
                    <button class="button green" onclick="aceitarPedido(this)">Aceitar</button>
                    <button class="button red">Rejeitar</button>
                </div>
            </li>
            <li class="service-item">
                <span>Mesa <?php echo gerarNumeroAleatorio(); ?></span>
                <div>
                    <button class="button green" onclick="aceitarPedido(this)">Aceitar</button>
                    <button class="button red">Rejeitar</button>
                </div>
            </li>
            <li class="service-item">
                <span>Mesa <?php echo gerarNumeroAleatorio(); ?></span>
                <div>
                    <button class="button green" onclick="aceitarPedido(this)">Aceitar</button>
                    <button class="button red">Rejeitar</button>
                </div>
            </li>
        </ol>
    </div>
</div>

<!-- Rodapé -->
<footer>
    <p>Bem-vindo, Restaurante <?php echo htmlspecialchars($restaurantId); ?>!</p>
    <p>Sua senha é: <?php echo htmlspecialchars($password); ?></p>
</footer>
</body>
</html>