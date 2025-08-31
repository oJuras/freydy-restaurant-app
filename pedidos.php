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
    <script src="assets/js/notifications.js"></script>
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
            fetch(`api/pedidos/detalhes-completos.php?id=${pedidoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const pedido = data.pedido;
                        
                        // Gerar HTML dos itens
                        let itensHtml = '';
                        if (pedido.itens && pedido.itens.length > 0) {
                            itensHtml = `
                                <div class="itens-pedido">
                                    ${pedido.itens.map(item => `
                                        <div class="item-pedido">
                                            <div class="item-info">
                                                <span class="item-nome">${item.nome_produto}</span>
                                                <span class="item-categoria">${item.categoria}</span>
                                            </div>
                                            <div class="item-detalhes">
                                                <span class="item-quantidade">${item.quantidade}x</span>
                                                <span class="item-preco">${item.preco_unitario_formatado}</span>
                                                <span class="item-subtotal">${item.subtotal_formatado}</span>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            `;
                        } else {
                            itensHtml = '<div class="no-data">Nenhum item</div>';
                        }
                        
                        // Gerar HTML do histórico
                        let historicoHtml = '';
                        if (pedido.historico && pedido.historico.length > 0) {
                            historicoHtml = `
                                <div class="historico-pedido">
                                    ${pedido.historico.map(h => `
                                        <div class="historico-item">
                                            <div class="historico-info">
                                                <span class="historico-data">${h.data_formatada}</span>
                                                <span class="historico-usuario">${h.usuario_nome || 'Sistema'}</span>
                                            </div>
                                            <div class="historico-status">
                                                <span class="status-anterior">${h.status_anterior_formatado}</span>
                                                <i class="fas fa-arrow-right"></i>
                                                <span class="status-novo">${h.status_novo_formatado}</span>
                                            </div>
                                            ${h.observacao ? `<div class="historico-observacao">${h.observacao}</div>` : ''}
                                        </div>
                                    `).join('')}
                                </div>
                            `;
                        } else {
                            historicoHtml = '<div class="no-data">Sem histórico</div>';
                        }
                        
                        const content = `
                            <div class="pedido-detalhes">
                                <div class="form-group">
                                    <label><strong>Número do Pedido:</strong></label>
                                    <p>${pedido.numero_pedido}</p>
                                </div>
                                <div class="form-group">
                                    <label><strong>Mesa:</strong></label>
                                    <p>Mesa ${pedido.numero_mesa}</p>
                                </div>
                                <div class="form-group">
                                    <label><strong>Status:</strong></label>
                                    <span class="status-badge status-${pedido.status}">${pedido.status.charAt(0).toUpperCase() + pedido.status.slice(1)}</span>
                                </div>
                                <div class="form-group">
                                    <label><strong>Valor Total:</strong></label>
                                    <p>${pedido.valor_total_formatado}</p>
                                </div>
                                <div class="form-group">
                                    <label><strong>Data:</strong></label>
                                    <p>${pedido.data_pedido_formatada}</p>
                                </div>
                                <div class="form-group">
                                    <label><strong>Atendente:</strong></label>
                                    <p>${pedido.nome_usuario}</p>
                                </div>
                                ${pedido.observacoes ? `
                                <div class="form-group">
                                    <label><strong>Observações:</strong></label>
                                    <p>${pedido.observacoes}</p>
                                </div>
                                ` : ''}
                                <div class="form-group">
                                    <label><strong>Itens do Pedido:</strong></label>
                                    ${itensHtml}
                                </div>
                                <div class="form-group">
                                    <label><strong>Histórico do Pedido:</strong></label>
                                    ${historicoHtml}
                                </div>
                            </div>
                        `;
                        modalSystem.open('modalDetalhes', `Detalhes do Pedido #${pedido.numero_pedido}`, content);
                    } else {
                        showNotification('Erro ao carregar detalhes do pedido', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showNotification('Erro ao carregar detalhes do pedido', 'error');
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
                            showNotification('Status do pedido atualizado com sucesso!', 'success');
                            location.reload();
                        } else {
                            showNotification('Erro ao atualizar status: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao atualizar status do pedido', 'error');
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
                showNotification('Selecione a mesa e ao menos um produto válido.', 'warning');
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
                    showNotification('Pedido criado com sucesso!', 'success');
                    location.reload();
                } else {
                    showNotification('Erro: ' + data.message, 'error');
                }
            });
        }
    </script>
</body>
</html>
