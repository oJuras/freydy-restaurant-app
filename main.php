<?php
// Inicia a sessão
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['restaurantId'])) {
    // Se não estiver logado, redireciona para o login
    header("Location: login.php");
    exit();
}

// Obtém as credenciais da sessão
$restaurantId = $_SESSION['restaurantId'];
$password = $_SESSION['password'];

// Função para gerar um número aleatório para a mesa
function gerarNumeroAleatorio() {
    return rand(1, 100); // Gera um número aleatório entre 1 e 100
}

// Função para gerar uma descrição genérica para o pedido
function gerarDescricaoPedido() {
    $descricoes = [
        "Pedido com diversos itens de comida e bebidas.",
        "Mesa com aperitivos e prato principal.",
        "Pedido de entrada, prato principal e sobremesa.",
        "Pedido variado com bebidas e entradas.",
        "Pedido com especialidades da casa.",
        "Mesa com prato vegetariano e sucos naturais.",
    ];
    return $descricoes[array_rand($descricoes)]; // Retorna uma descrição aleatória
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <style>
        /* Resetando o estilo básico */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Container principal */
        .container {
            display: flex;
            height: 100vh;
            flex-direction: row; /* Alinha o conteúdo na horizontal */
        }

        /* Abas */
        .tabs {
            display: flex;
            justify-content: space-around;
            background-color: #8B3A3A; /* Cor vinho */
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
            background-color: #7a2a2a; /* Cor de hover */
        }

        .tabs .active {
            background-color: #5c1f1f; /* Cor ativa */
        }

        /* Container das abas de conteúdo */
        .content {
            display: none;
            padding: 20px;
            background-color: #f4f4f4;
            width: 70%; /* 70% da largura para conteúdo principal */
        }

        .content.active {
            display: block;
        }

        /* Aprovação */
        .approval {
            width: 30%; /* 30% para a lista de aprovação */
            padding: 20px;
            background-color: #f4f4f4;
            border-left: 5px solid #8B3A3A; /* Linha vermelho vinho */
        }

        /* Títulos */
        h1, h2 {
            color: #8B3A3A; /* Cor vinho */
        }

        /* Estilo para as listas */
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

        /* Botões */
        .button {
            padding: 8px 15px;
            border-radius: 4px;
            color: white;
            border: none;
            cursor: pointer;
            margin-left: 10px;
        }

        .button.green {
            background-color: #28a745; /* Verde */
        }

        .button.red {
            background-color: #dc3545; /* Vermelho */
        }

        .button.print {
            background-color: #007bff; /* Azul */
        }

        /* Estilo do rodapé */
        footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #8B3A3A;
        }

        /* Estilo para os botões na lista de aprovação */
        .approval .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .approval .service-item div {
            display: flex;
            gap: 10px; /* Espaçamento entre os botões */
        }

        /* Estilo para os filtros */
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
    </style>
</head>
<body>

<!-- Container das abas -->
<div class="tabs">
    <div id="tabAndamento" class="active" onclick="changeTab('andamento')">Pedidos em Andamento</div>
    <div id="tabConcluidos" onclick="changeTab('concluidos')">Pedidos Concluídos</div>
    <div id="tabFiltros" onclick="changeTab('filtros')">Filtros</div>
</div>

<div class="container">
    <!-- Conteúdo dos pedidos em andamento -->
    <div id="andamento" class="content active">
        <h1>Pedidos em Andamento</h1>

        <ol id="pedidoEmAndamentoList">
            <li class="order-item">
                <span class="order-id">Mesa #<?php echo gerarNumeroAleatorio(); ?></span>
                <span class="order-description"><?php echo gerarDescricaoPedido(); ?></span>
                <button class="button green" onclick="finalizarPedido(this)">Finalizar</button>
                <button class="button print" onclick="imprimirComanda(this)">Imprimir Comanda</button>
            </li>
            <li class="order-item">
                <span class="order-id">Mesa #<?php echo gerarNumeroAleatorio(); ?></span>
                <span class="order-description"><?php echo gerarDescricaoPedido(); ?></span>
                <button class="button green" onclick="finalizarPedido(this)">Finalizar</button>
                <button class="button print" onclick="imprimirComanda(this)">Imprimir Comanda</button>
            </li>
            <li class="order-item">
                <span class="order-id">Mesa #<?php echo gerarNumeroAleatorio(); ?></span>
                <span class="order-description"><?php echo gerarDescricaoPedido(); ?></span>
                <button class="button green" onclick="finalizarPedido(this)">Finalizar</button>
                <button class="button print" onclick="imprimirComanda(this)">Imprimir Comanda</button>
            </li>
        </ol>
    </div>

    <!-- Conteúdo dos pedidos concluídos -->
    <div id="concluidos" class="content">
        <h1>Pedidos Concluídos</h1>
        <ol id="pedidoConcluidoList">
            <li class="order-item">
                <span class="order-id">Mesa #<?php echo gerarNumeroAleatorio(); ?></span>
                <span class="order-description"><?php echo gerarDescricaoPedido(); ?></span>
                <button class="button red" onclick="retornarPedido(this)">Retornar para Em Andamento</button>
            </li>
            <li class="order-item">
                <span class="order-id">Mesa #<?php echo gerarNumeroAleatorio(); ?></span>
                <span class="order-description"><?php echo gerarDescricaoPedido(); ?></span>
                <button class="button red" onclick="retornarPedido(this)">Retornar para Em Andamento</button>
            </li>
            <li class="order-item">
                <span class="order-id">Mesa #<?php echo gerarNumeroAleatorio(); ?></span>
                <span class="order-description"><?php echo gerarDescricaoPedido(); ?></span>
                <button class="button red" onclick="retornarPedido(this)">Retornar para Em Andamento</button>
            </li>
        </ol>
    </div>

    <!-- Conteúdo de filtros -->
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

        <!-- Lista de pedidos filtrados -->
        <h2>Pedidos Filtrados</h2>
        <ol id="filtroResultados">
            <!-- Os pedidos filtrados serão adicionados aqui -->
        </ol>
    </div>

    <!-- Lista de Aprovação -->
    <div class="approval">
        <h2>Aprovação</h2>
        <ol>
            <?php for ($i = 1; $i <= 3; $i++) { ?>
                <li class="service-item">
                    <span>Mesa <?php echo gerarNumeroAleatorio(); ?></span>
                    <div>
                        <button class="button green">Aceitar</button>
                        <button class="button red">Rejeitar</button>
                    </div>
                </li>
            <?php } ?>
        </ol>
    </div>
</div>

<!-- Rodapé -->
<footer>
    <p>Bem-vindo, Restaurante <?php echo htmlspecialchars($restaurantId); ?>!</p>
    <p>Sua senha é: <?php echo htmlspecialchars($password); ?></p>
</footer>

<script>
    // Função para alternar entre as abas
    function changeTab(tabName) {
        // Esconde todos os conteúdos
        document.querySelectorAll('.content').forEach(function(content) {
            content.classList.remove('active');
        });
        
        // Remove a classe 'active' das abas
        document.querySelectorAll('.tabs div').forEach(function(tab) {
            tab.classList.remove('active');
        });

        // Mostra o conteúdo correspondente à aba selecionada
        document.getElementById(tabName).classList.add('active');

        // Marca a aba como ativa
        document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).classList.add('active');
    }

    // Função para finalizar o pedido e mover para pedidos concluídos
    function finalizarPedido(button) {
        const pedido = button.closest('.order-item');
        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList');
        const listaConcluidos = document.getElementById('pedidoConcluidoList');

        // Remove o pedido da lista de andamento
        listaEmAndamento.removeChild(pedido);

        // Adiciona o pedido na lista de concluídos
        const clonePedido = pedido.cloneNode(true);
        // Remover o botão "Imprimir Comanda" no pedido concluído
        const imprimirButton = clonePedido.querySelector('.button.print');
        if (imprimirButton) {
            clonePedido.removeChild(imprimirButton);
        }

        const retornarButton = clonePedido.querySelector('.button');
        retornarButton.textContent = "Retornar para Em Andamento";
        retornarButton.classList.remove('green');
        retornarButton.classList.add('red');
        retornarButton.setAttribute("onclick", "retornarPedido(this)");

        listaConcluidos.appendChild(clonePedido);
    }

    // Função para retornar o pedido para "Em Andamento"
    function retornarPedido(button) {
        const pedido = button.closest('.order-item');
        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList');
        const listaConcluidos = document.getElementById('pedidoConcluidoList');

        // Remove o pedido da lista de concluídos
        listaConcluidos.removeChild(pedido);

        // Adiciona o pedido de volta na lista de em andamento
        const clonePedido = pedido.cloneNode(true);
        const finalizarButton = clonePedido.querySelector('.button');
        finalizarButton.textContent = "Finalizar";
        finalizarButton.classList.remove('red');
        finalizarButton.classList.add('green');
        finalizarButton.setAttribute("onclick", "finalizarPedido(this)");

        // Recria o botão "Imprimir Comanda" no pedido retornado
        const imprimirButton = document.createElement('button');
        imprimirButton.classList.add('button', 'print');
        imprimirButton.textContent = "Imprimir Comanda";
        imprimirButton.setAttribute("onclick", "imprimirComanda(this)");

        // Adiciona o botão "Imprimir Comanda" ao pedido
        clonePedido.appendChild(imprimirButton);

        listaEmAndamento.appendChild(clonePedido);
    }

    // Função para aplicar os filtros
    function filtrarPedidos() {
        let mesa = document.getElementById('mesa').value;
        let status = document.getElementById('status').value;

        // Captura as listas de pedidos em andamento e concluídos
        const listaEmAndamento = document.getElementById('pedidoEmAndamentoList');
        const listaConcluidos = document.getElementById('pedidoConcluidoList');

        // Cria arrays para armazenar os pedidos filtrados
        let pedidosFiltrados = [];

        // Filtrando pedidos "Em Andamento"
        let pedidosAndamento = Array.from(listaEmAndamento.children).filter(pedido => {
            let mesaPedido = pedido.querySelector('.order-id').textContent.replace('Mesa #', '');
            let descricaoPedido = pedido.querySelector('.order-description').textContent;
            let correspondeMesa = mesa ? mesaPedido.includes(mesa) : true;
            let correspondeStatus = status ? "andamento" === status : true;

            return correspondeMesa && correspondeStatus;
        });

        // Filtrando pedidos "Concluídos"
        let pedidosConcluidos = Array.from(listaConcluidos.children).filter(pedido => {
            let mesaPedido = pedido.querySelector('.order-id').textContent.replace('Mesa #', '');
            let descricaoPedido = pedido.querySelector('.order-description').textContent;
            let correspondeMesa = mesa ? mesaPedido.includes(mesa) : true;
            let correspondeStatus = status ? "concluido" === status : true;

            return correspondeMesa && correspondeStatus;
        });

        // Combina os pedidos filtrados de ambas as listas
        pedidosFiltrados = [...pedidosAndamento, ...pedidosConcluidos];

        // Limpa a lista de resultados antes de adicionar os filtrados
        const listaFiltros = document.getElementById('filtroResultados');
        listaFiltros.innerHTML = '';

        // Adiciona os pedidos filtrados à aba de filtros
        pedidosFiltrados.forEach(pedido => {
            const novoItem = document.createElement('li');
            novoItem.classList.add('order-item');
            novoItem.innerHTML = pedido.innerHTML; // Copia o conteúdo do pedido
            listaFiltros.appendChild(novoItem);
        });

        // Exibe a aba de filtros
        changeTab('filtros');
    }
</script>

</body>
</html>
