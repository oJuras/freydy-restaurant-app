<?php
/**
 * Gerenciamento de Pedidos
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

// Carrega modelos
require_once 'models/Pedido.php';
require_once 'models/Mesa.php';
require_once 'models/Produto.php';

$pedidoModel = new Pedido();
$mesaModel = new Mesa();
$produtoModel = new Produto();

// Busca pedidos do restaurante
$pedidos = $pedidoModel->listarPorRestaurante($usuario['restaurante_id']);
$mesas = $mesaModel->listarPorRestaurante($usuario['restaurante_id']);
$produtos = $produtoModel->listarPorRestaurante($usuario['restaurante_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Gerenciamento de Pedidos</h1>
                <button class="btn btn-primary" onclick="abrirModalNovoPedido()">
                    <i class="fas fa-plus"></i> Novo Pedido
                </button>
            </div>
            
            <!-- Filtros -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="filtro-status">Status:</label>
                    <select id="filtro-status" onchange="filtrarPedidos()">
                        <option value="">Todos</option>
                        <option value="pendente">Pendente</option>
                        <option value="em_preparo">Em Preparo</option>
                        <option value="pronto">Pronto</option>
                        <option value="entregue">Entregue</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filtro-mesa">Mesa:</label>
                    <select id="filtro-mesa" onchange="filtrarPedidos()">
                        <option value="">Todas</option>
                        <?php foreach ($mesas as $mesa): ?>
                            <option value="<?php echo $mesa['numero']; ?>">Mesa <?php echo $mesa['numero']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Lista de Pedidos -->
            <div class="pedidos-grid">
                <?php if (empty($pedidos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-utensils"></i>
                        <h3>Nenhum pedido encontrado</h3>
                        <p>Comece criando um novo pedido.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($pedidos as $pedido): ?>
                        <div class="pedido-card" data-status="<?php echo $pedido['status']; ?>" data-mesa="<?php echo $pedido['mesa_numero']; ?>">
                            <div class="pedido-header">
                                <h3>Pedido #<?php echo $pedido['numero_pedido']; ?></h3>
                                <span class="status-badge status-<?php echo $pedido['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $pedido['status'])); ?>
                                </span>
                            </div>
                            
                            <div class="pedido-info">
                                <p><strong>Mesa:</strong> <?php echo $pedido['mesa_numero']; ?></p>
                                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></p>
                                <p><strong>Valor:</strong> R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></p>
                            </div>
                            
                            <div class="pedido-actions">
                                <button class="btn btn-sm btn-info" onclick="verDetalhesPedido(<?php echo $pedido['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                
                                <?php if ($pedido['status'] == 'pendente'): ?>
                                    <button class="btn btn-sm btn-warning" onclick="atualizarStatusPedido(<?php echo $pedido['id']; ?>, 'em_preparo')">
                                        <i class="fas fa-fire"></i> Preparar
                                    </button>
                                <?php elseif ($pedido['status'] == 'em_preparo'): ?>
                                    <button class="btn btn-sm btn-success" onclick="atualizarStatusPedido(<?php echo $pedido['id']; ?>, 'pronto')">
                                        <i class="fas fa-check"></i> Pronto
                                    </button>
                                <?php elseif ($pedido['status'] == 'pronto'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="atualizarStatusPedido(<?php echo $pedido['id']; ?>, 'entregue')">
                                        <i class="fas fa-truck"></i> Entregar
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (in_array($pedido['status'], ['pendente', 'em_preparo'])): ?>
                                    <button class="btn btn-sm btn-danger" onclick="atualizarStatusPedido(<?php echo $pedido['id']; ?>, 'cancelado')">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal de Detalhes do Pedido -->
    <div id="modalDetalhes" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="detalhesPedido"></div>
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script>
        function filtrarPedidos() {
            const status = document.getElementById('filtro-status').value;
            const mesa = document.getElementById('filtro-mesa').value;
            const cards = document.querySelectorAll('.pedido-card');
            
            cards.forEach(card => {
                let mostrar = true;
                
                if (status && card.dataset.status !== status) {
                    mostrar = false;
                }
                
                if (mesa && card.dataset.mesa !== mesa) {
                    mostrar = false;
                }
                
                card.style.display = mostrar ? 'block' : 'none';
            });
        }
        
        function verDetalhesPedido(pedidoId) {
            fetch('api/pedidos/detalhes.php?id=' + pedidoId)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        alert('Erro: ' + data.message);
                        return;
                    }
                    const p = data.pedido;
                    const historico = data.historico;
                    let itensHtml = '';
                    if (p.itens && p.itens.length) {
                        itensHtml = `<table class='table'><thead><tr><th>Produto</th><th>Categoria</th><th>Qtd</th><th>Unitário</th><th>Total</th></tr></thead><tbody>`;
                        p.itens.forEach(item => {
                            itensHtml += `<tr><td>${item.nome_produto}</td><td>${item.categoria}</td><td>${item.quantidade}</td><td>R$ ${parseFloat(item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td><td>R$ ${(item.quantidade*item.preco_unitario).toLocaleString('pt-BR', {minimumFractionDigits:2})}</td></tr>`;
                        });
                        itensHtml += '</tbody></table>';
                    } else {
                        itensHtml = '<div class="no-data">Nenhum item</div>';
                    }
                    let historicoHtml = '';
                    if (historico && historico.length) {
                        historicoHtml = `<ul style='padding-left:18px;'>`;
                        historico.forEach(h => {
                            historicoHtml += `<li><b>${h.status_novo}</b> por ${h.nome_usuario} em ${new Date(h.data_mudanca).toLocaleString('pt-BR')} ${h.observacao ? '<br><small>'+h.observacao+'</small>' : ''}</li>`;
                        });
                        historicoHtml += '</ul>';
                    } else {
                        historicoHtml = '<div class="no-data">Sem histórico</div>';
                    }
                    const content = `
                        <div class='pedido-detalhes'>
                            <div class='form-group'><label><b>Número:</b></label> <span>${p.numero_pedido}</span></div>
                            <div class='form-group'><label><b>Status:</b></label> <span class='status-badge status-${p.status}'>${p.status}</span></div>
                            <div class='form-group'><label><b>Mesa:</b></label> <span>${p.numero_mesa}</span></div>
                            <div class='form-group'><label><b>Data:</b></label> <span>${new Date(p.data_pedido).toLocaleString('pt-BR')}</span></div>
                            <div class='form-group'><label><b>Valor Total:</b></label> <span>R$ ${parseFloat(p.valor_total).toLocaleString('pt-BR', {minimumFractionDigits:2})}</span></div>
                            <div class='form-group'><label><b>Observação:</b></label> <span>${p.observacao || '-'}</span></div>
                            <div class='form-group'><label><b>Itens:</b></label> ${itensHtml}</div>
                            <div class='form-group'><label><b>Histórico:</b></label> ${historicoHtml}</div>
                        </div>
                    `;
                    modalSystem.open('modalDetalhesPedido', 'Detalhes do Pedido', content);
                });
        }
        
        function atualizarStatusPedido(pedidoId, novoStatus) {
            const statusLabels = {
                'pendente': 'Pendente',
                'em_preparo': 'Em Preparo',
                'pronto': 'Pronto',
                'entregue': 'Entregue',
                'cancelado': 'Cancelado'
            };
            
            modalSystem.confirm(
                'Atualizar Status',
                `Deseja realmente alterar o status do pedido para "${statusLabels[novoStatus]}"?`,
                () => {
                    fetch('api/pedidos/atualizar-status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            pedido_id: pedidoId,
                            status: novoStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro ao atualizar status: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao atualizar status do pedido');
                    });
                }
            );
        }
        
        // Dados para o modal de novo pedido
        const mesasData = <?php echo json_encode($mesas); ?>;
        const produtosData = <?php echo json_encode($produtos); ?>;

        function abrirModalNovoPedido() {
            let produtosOptions = produtosData.map(p =>
                `<option value="${p.id}" data-preco="${p.preco}">${p.nome} (R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')})</option>`
            ).join('');
            let mesasOptions = mesasData.map(m =>
                `<option value="${m.id}">Mesa ${m.numero}</option>`
            ).join('');
            const content = `
                <form id="formNovoPedido">
                    <div class="form-group">
                        <label for="mesa_id">Mesa *</label>
                        <select id="mesa_id" name="mesa_id" required>
                            <option value="">Selecione a mesa</option>
                            ${mesasOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Produtos *</label>
                        <div id="itensPedido"></div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="adicionarItemPedido()"><i class="fas fa-plus"></i> Adicionar Produto</button>
                    </div>
                    <div class="form-group">
                        <label for="observacao">Observação</label>
                        <textarea id="observacao" name="observacao" rows="2" placeholder="Observações do pedido..."></textarea>
                    </div>
                    <div class="form-group">
                        <strong>Total: R$ <span id="valorTotalPedido">0,00</span></strong>
                    </div>
                </form>
            `;
            modalSystem.openForm('modalNovoPedido', 'Novo Pedido', content, 'salvarNovoPedido()');
            // Adiciona o primeiro item por padrão
            setTimeout(() => adicionarItemPedido(), 100);
        }

        function removerItemPedido(btn) {
            if (btn.parentNode) {
                btn.parentNode.remove();
                atualizarTotalPedido();
            }
        }

        function adicionarItemPedido() {
            const idx = document.querySelectorAll('.item-pedido-row').length;
            let produtosOptions = produtosData.map(p =>
                `<option value="${p.id}" data-preco="${p.preco}">${p.nome} (R$ ${parseFloat(p.preco).toFixed(2).replace('.', ',')})</option>`
            ).join('');
            const row = document.createElement('div');
            row.className = 'item-pedido-row';
            row.innerHTML = `
                <select class="produto-select" onchange="atualizarTotalPedido()">
                    <option value="">Produto</option>
                    ${produtosOptions}
                </select>
                <input type="number" class="qtd-input" min="1" value="1" style="width:60px;" onchange="atualizarTotalPedido()">
                <button type="button" class="btn btn-sm btn-danger" onclick="removerItemPedido(this)"><i class="fas fa-trash"></i></button>
            `;
            document.getElementById('itensPedido').appendChild(row);
            atualizarTotalPedido();
        }

        function atualizarTotalPedido() {
            let total = 0;
            document.querySelectorAll('.item-pedido-row').forEach(row => {
                const select = row.querySelector('.produto-select');
                const qtd = parseInt(row.querySelector('.qtd-input').value) || 1;
                const produto = produtosData.find(p => p.id == select.value);
                if (produto) {
                    total += produto.preco * qtd;
                }
            });
            document.getElementById('valorTotalPedido').innerText = total.toLocaleString('pt-BR', {minimumFractionDigits:2});
        }

        function salvarNovoPedido() {
            const form = document.getElementById('formNovoPedido');
            const mesaId = form.mesa_id.value;
            const observacao = form.observacao.value;
            const itens = [];
            let valid = true;
            document.querySelectorAll('.item-pedido-row').forEach(row => {
                const select = row.querySelector('.produto-select');
                const qtd = parseInt(row.querySelector('.qtd-input').value) || 1;
                if (select.value) {
                    itens.push({ produto_id: select.value, quantidade: qtd });
                } else {
                    valid = false;
                }
            });
            if (!mesaId || itens.length === 0 || !valid) {
                alert('Selecione a mesa e ao menos um produto válido.');
                return;
            }
            fetch('api/pedidos/criar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mesa_id: mesaId, itens, observacao })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
