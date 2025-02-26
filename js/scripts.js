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
