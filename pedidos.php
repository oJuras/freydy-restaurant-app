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
            // Por enquanto, mostra um alert simples
            // Em uma implementação completa, buscaria os detalhes via API
            alert('Detalhes do pedido ' + pedidoId + '\n\nFuncionalidade de detalhes será implementada em breve.');
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
                            novo_status: novoStatus
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
        
        function abrirModalNovoPedido() {
            const content = `
                <div class="novo-pedido-info">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Funcionalidade em Desenvolvimento</strong>
                        <p>A criação de novos pedidos será implementada em breve. Esta funcionalidade permitirá:</p>
                        <ul>
                            <li>Selecionar mesa</li>
                            <li>Adicionar produtos ao pedido</li>
                            <li>Definir quantidades</li>
                            <li>Adicionar observações</li>
                            <li>Calcular valor total</li>
                        </ul>
                    </div>
                </div>
            `;
            
            modalSystem.open('modalNovoPedido', 'Novo Pedido', content);
        }
    </script>
</body>
</html>
