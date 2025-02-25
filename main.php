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
    <style>
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            height: 100vh;
            flex-direction: row; 
        }

        
        .tabs {
            display: flex;
            justify-content: space-around;
            background-color: #8B3A3A;
            padding: 10px;
            cursor: pointer;
            width: 100%;
        }

        .tabs div {
            color: white;
            padding: 10px;
            text-align: center;
            width: 33%;
        }

        .tabs div:hover {
            background-color: #7a2a2a;
        }

        .tabs .active {
            background-color: #5c1f1f; 
        }

        .content {
            display: none;
            padding: 20px;
            background-color: #f4f4f4;
            width: 70%; 
        }

        .content.active {
            display: block;
        }

        .approval {
            width: 30%; 
            padding: 20px;
            background-color: #f4f4f4;
            border-left: 5px solid #8B3A3A; 
        }

        h1, h2 {
            color: #8B3A3A; 
        }

        ol {
            list-style-position: inside;
            margin: 0;
            padding: 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .order-id, .order-description {
            flex: 1;
        }

        .button {
            padding: 8px 15px;
            border-radius: 4px;
            color: white;
            border: none;
            cursor: pointer;
            margin-left: 10px;
        }

        .button.green {
            background-color: #28a745; 
        }

        .button.red {
            background-color: #dc3545; 
        }

        .button.print {
            background-color: #007bff;
        }

        footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #8B3A3A;
        }

        .approval .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .approval .service-item div {
            display: flex;
            gap: 10px; 
        }

        .filters {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }

        .filters select, .filters input {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        #filtroResultados .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border-radius: 8px; 
            background-color: #f9f9f9; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); 
            margin-bottom: 10px;
            transition: background-color 0.3s ease; 
            width: 100%; 
            box-sizing: border-box; 
            font-size: 14px;
        }

        #filtroResultados .order-item:hover {
            background-color: #e0f7fa; 
        }

        .pedido-container {
            display: flex;
            justify-content: space-between; 
            align-items: center; 
            width: 100%;
        }

        .pedido-text {
            flex-grow: 1; 
            text-align: left;
            color: #333; 
            font-weight: 500; 
            overflow: hidden;
            text-overflow: ellipsis; 
            white-space: nowrap; 
        }

        .pedido-status {
            text-align: right;
            color: #007BFF; 
            font-weight: bold;
            text-transform: uppercase;
        }
    </style>
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

<script>
    function changeTab(tabName) {
        document.querySelectorAll('.content').forEach(function(content) {
            content.classList.remove('active');
        });
        
        document.querySelectorAll('.tabs div').forEach(function(tab) {
            tab.classList.remove('active');
        });

        document.getElementById(tabName).classList.add('active');

        document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).classList.add('active');
    }

    function finalizarPedido(button, pedidoId) {
        const pedido = button.closest('.order-item');
        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList');
        const listaConcluidos = document.getElementById('pedidoConcluidoList');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'atualizar_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                listaEmAndamento.removeChild(pedido);

                const clonePedido = pedido.cloneNode(true);
                const imprimirButton = clonePedido.querySelector('.button.print');
                if (imprimirButton) {
                    clonePedido.removeChild(imprimirButton);
                }

                const retornarButton = clonePedido.querySelector('.button');
                retornarButton.textContent = "Retornar para Em Andamento";
                retornarButton.classList.remove('green');
                retornarButton.classList.add('red');
                retornarButton.setAttribute("onclick", `retornarPedido(this, '${pedidoId}')`);

                listaConcluidos.appendChild(clonePedido);
            }
        };
        xhr.send(JSON.stringify({ id: pedidoId, status: 'Concluído' }));
    }

    function retornarPedido(button, pedidoId) {
        const pedido = button.closest('.order-item');
        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList');
        const listaConcluidos = document.getElementById('pedidoConcluidoList');

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'atualizar_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                listaConcluidos.removeChild(pedido);

                const clonePedido = pedido.cloneNode(true);
                const finalizarButton = clonePedido.querySelector('.button');
                finalizarButton.textContent = "Finalizar";
                finalizarButton.classList.remove('red');
                finalizarButton.classList.add('green');
                finalizarButton.setAttribute("onclick", `finalizarPedido(this, '${pedidoId}')`);

                const imprimirButton = document.createElement('button');
                imprimirButton.classList.add('button', 'print');
                imprimirButton.textContent = "Imprimir Comanda";
                imprimirButton.setAttribute("onclick", "imprimirComanda(this)");

                clonePedido.appendChild(imprimirButton);
                listaEmAndamento.appendChild(clonePedido);
            }
        };
        xhr.send(JSON.stringify({ id: pedidoId, status: 'Em Andamento' }));
    }

    function aceitarPedido(button) {
        const pedido = button.closest('.service-item'); 
        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList'); 
        
        const numeroMesa = gerarNumeroAleatorio();
        const descricaoPedido = gerarDescricaoPedido();
        
        const novoPedido = {
            Mesa: numeroMesa,
            Itens: [descricaoPedido], 
            Status: 'Em Andamento'
        };

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'salvar_pedido.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                console.log('Pedido salvo no banco de dados');
                const novoItemPedido = document.createElement('li');
                novoItemPedido.classList.add('order-item');
                novoItemPedido.innerHTML = `
                    <span class="order-id">Mesa #${numeroMesa}</span>
                    <span class="order-description">${descricaoPedido}</span>
                    <button class="button green" onclick="finalizarPedido(this)">Finalizar</button>
                    <button class="button print" onclick="imprimirComanda(this)">Imprimir Comanda</button>
                `;
                listaEmAndamento.appendChild(novoItemPedido);
                pedido.remove(); 
            }
        };
        xhr.send(JSON.stringify(novoPedido));
    }

    function gerarNumeroAleatorio() {
        return Math.floor(Math.random() * 100) + 1; 
    }

    function gerarDescricaoPedido() {
        const descricoes = [
            "Pedido com diversos itens de comida e bebidas.",
            "Mesa com aperitivos e prato principal.",
            "Pedido de entrada, prato principal e sobremesa.",
            "Pedido variado com bebidas e entradas.",
            "Pedido com especialidades da casa.",
            "Mesa com prato vegetariano e sucos naturais."
        ];
        return descricoes[Math.floor(Math.random() * descricoes.length)];
    }

    function filtrarPedidos() {
        let mesa = document.getElementById('mesa').value;
        let status = document.getElementById('status').value;

        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList');
        const listaConcluidos = document.getElementById('pedidoConcluidoList');

        let pedidosFiltrados = [];

        let pedidosAndamento = Array.from(listaEmAndamento.children).filter(pedido => {
            let mesaPedido = pedido.querySelector('.order-id').textContent.replace('Mesa #', '');
            let descricaoPedido = pedido.querySelector('.order-description').textContent;
            let correspondeMesa = mesa ? mesaPedido.includes(mesa) : true;
            let correspondeStatus = status ? "andamento" === status : true;

            return correspondeMesa && correspondeStatus;
        });

        let pedidosConcluidos = Array.from(listaConcluidos.children).filter(pedido => {
            let mesaPedido = pedido.querySelector('.order-id').textContent.replace('Mesa #', '');
            let descricaoPedido = pedido.querySelector('.order-description').textContent;
            let correspondeMesa = mesa ? mesaPedido.includes(mesa) : true;
            let correspondeStatus = status ? "concluido" === status : true;

            return correspondeMesa && correspondeStatus;
        });

        pedidosFiltrados = [...pedidosAndamento, ...pedidosConcluidos];

        const listaFiltros = document.getElementById('filtroResultados');
        listaFiltros.innerHTML = '';

        pedidosFiltrados.forEach(pedido => {
            const novoItem = document.createElement('li');
            novoItem.classList.add('order-item');
            
            let numeroMesa = pedido.querySelector('.order-id').textContent.replace('Mesa #', '');
            let descricaoPedido = pedido.querySelector('.order-description').textContent;
            let statusPedido = pedido.closest('.content').id === 'andamento' ? 'Em Andamento' : 'Concluído';

            novoItem.innerHTML = `
                <div class="pedido-container">
                    <span class="pedido-text">Mesa ${numeroMesa} - ${descricaoPedido}</span>
                    <span class="pedido-status">${statusPedido}</span>
                </div>
            `;

            listaFiltros.appendChild(novoItem);
        });

        changeTab('filtros');
    }
</script>

</body>
</html>