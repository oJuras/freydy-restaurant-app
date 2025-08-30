<?php
/**
 * Dashboard Principal
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

// Busca dados para o dashboard
$pedidosPendentes = $pedidoModel->listarPorRestaurante($usuario['restaurante_id'], 'pendente', 10);
$pedidosEmPreparo = $pedidoModel->listarPorRestaurante($usuario['restaurante_id'], 'em_preparo', 10);
$pedidosProntos = $pedidoModel->listarPorRestaurante($usuario['restaurante_id'], 'pronto', 10);

$mesasLivres = $mesaModel->listarLivres($usuario['restaurante_id']);
$mesasOcupadas = $mesaModel->listarOcupadas($usuario['restaurante_id']);

$estatisticasPedidos = $pedidoModel->calcularEstatisticas($usuario['restaurante_id']);
$estatisticasMesas = $mesaModel->calcularEstatisticas($usuario['restaurante_id']);
$estatisticasProdutos = $produtoModel->calcularEstatisticas($usuario['restaurante_id']);

$produtosMaisVendidos = $produtoModel->listarMaisVendidos($usuario['restaurante_id'], 5, 7);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Dashboard</h1>
                <p>Bem-vindo, <?php echo htmlspecialchars($usuario['nome']); ?>!</p>
            </div>
            
            <!-- Cards de Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $estatisticasPedidos['total_pedidos'] ?? 0; ?></h3>
                        <p>Total de Pedidos</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($pedidosPendentes); ?></h3>
                        <p>Pedidos Pendentes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-fire"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($pedidosEmPreparo); ?></h3>
                        <p>Em Preparo</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($pedidosProntos); ?></h3>
                        <p>Prontos para Entrega</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $estatisticasMesas['mesas_livres'] ?? 0; ?></h3>
                        <p>Mesas Livres</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>R$ <?php echo number_format($estatisticasPedidos['valor_total'] ?? 0, 2, ',', '.'); ?></h3>
                        <p>Faturamento Total</p>
                    </div>
                </div>
            </div>
            
            <!-- Seções de Pedidos -->
            <div class="dashboard-sections">
                <!-- Pedidos Pendentes -->
                <div class="section-card">
                    <div class="section-header">
                        <h2><i class="fas fa-clock"></i> Pedidos Pendentes</h2>
                        <a href="pedidos.php?status=pendente" class="btn btn-sm btn-primary">Ver Todos</a>
                    </div>
                    <div class="section-content">
                        <?php if (empty($pedidosPendentes)): ?>
                            <p class="no-data">Nenhum pedido pendente</p>
                        <?php else: ?>
                            <?php foreach (array_slice($pedidosPendentes, 0, 5) as $pedido): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-number">#<?php echo $pedido['numero_pedido']; ?></span>
                                        <span class="order-mesa">Mesa <?php echo $pedido['numero_mesa']; ?></span>
                                        <span class="order-value">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                                    </div>
                                    <div class="order-actions">
                                        <button class="btn btn-sm btn-success" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'em_preparo')">
                                            <i class="fas fa-fire"></i> Preparar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pedidos em Preparo -->
                <div class="section-card">
                    <div class="section-header">
                        <h2><i class="fas fa-fire"></i> Em Preparo</h2>
                        <a href="pedidos.php?status=em_preparo" class="btn btn-sm btn-primary">Ver Todos</a>
                    </div>
                    <div class="section-content">
                        <?php if (empty($pedidosEmPreparo)): ?>
                            <p class="no-data">Nenhum pedido em preparo</p>
                        <?php else: ?>
                            <?php foreach (array_slice($pedidosEmPreparo, 0, 5) as $pedido): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-number">#<?php echo $pedido['numero_pedido']; ?></span>
                                        <span class="order-mesa">Mesa <?php echo $pedido['numero_mesa']; ?></span>
                                        <span class="order-value">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                                    </div>
                                    <div class="order-actions">
                                        <button class="btn btn-sm btn-warning" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'pronto')">
                                            <i class="fas fa-check"></i> Pronto
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Pedidos Prontos -->
                <div class="section-card">
                    <div class="section-header">
                        <h2><i class="fas fa-check-circle"></i> Prontos para Entrega</h2>
                        <a href="pedidos.php?status=pronto" class="btn btn-sm btn-primary">Ver Todos</a>
                    </div>
                    <div class="section-content">
                        <?php if (empty($pedidosProntos)): ?>
                            <p class="no-data">Nenhum pedido pronto</p>
                        <?php else: ?>
                            <?php foreach (array_slice($pedidosProntos, 0, 5) as $pedido): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <span class="order-number">#<?php echo $pedido['numero_pedido']; ?></span>
                                        <span class="order-mesa">Mesa <?php echo $pedido['numero_mesa']; ?></span>
                                        <span class="order-value">R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></span>
                                    </div>
                                    <div class="order-actions">
                                        <button class="btn btn-sm btn-success" onclick="atualizarStatus(<?php echo $pedido['id']; ?>, 'entregue')">
                                            <i class="fas fa-truck"></i> Entregar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Produtos Mais Vendidos -->
            <div class="section-card">
                <div class="section-header">
                    <h2><i class="fas fa-chart-line"></i> Produtos Mais Vendidos (Últimos 7 dias)</h2>
                </div>
                <div class="section-content">
                    <?php if (empty($produtosMaisVendidos)): ?>
                        <p class="no-data">Nenhum dado disponível</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produto</th>
                                        <th>Categoria</th>
                                        <th>Quantidade Vendida</th>
                                        <th>Total de Pedidos</th>
                                        <th>Preço</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtosMaisVendidos as $produto): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                                            <td><?php echo htmlspecialchars($produto['categoria_nome']); ?></td>
                                            <td><?php echo $produto['total_vendido']; ?></td>
                                            <td><?php echo $produto['total_pedidos']; ?></td>
                                            <td>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
</body>
</html>
