<?php
require_once 'includes/auth.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>Relatórios</h1>
            </div>
            <div class="filters-section">
                <div class="filter-group">
                    <label for="data-inicio">De:</label>
                    <input type="date" id="data-inicio">
                </div>
                <div class="filter-group">
                    <label for="data-fim">Até:</label>
                    <input type="date" id="data-fim">
                </div>
                <button class="btn btn-primary" onclick="carregarRelatorios()"><i class="fas fa-search"></i> Filtrar</button>
            </div>
            <div id="relatoriosResumo" class="stats-grid"></div>
            <div class="dashboard-sections">
                <div class="section-card">
                    <h2>Pedidos por Status</h2>
                    <canvas id="graficoPedidosStatus" height="120"></canvas>
                </div>
                <div class="section-card">
                    <h2>Produtos Mais Vendidos</h2>
                    <canvas id="graficoProdutosVendidos" height="120"></canvas>
                </div>
                <div class="section-card">
                    <h2>Ocupação de Mesas</h2>
                    <canvas id="graficoMesasOcupadas" height="120"></canvas>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/dashboard.js"></script>
    <script>
        function carregarRelatorios() {
            const inicio = document.getElementById('data-inicio').value;
            const fim = document.getElementById('data-fim').value;
            fetch(`api/relatorios/geral.php?inicio=${inicio}&fim=${fim}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        renderResumo(data.resumo);
                        renderGraficos(data);
                    } else {
                        document.getElementById('relatoriosResumo').innerHTML = '<div class="no-data">Erro ao carregar relatórios</div>';
                    }
                });
        }
        function renderResumo(resumo) {
            document.getElementById('relatoriosResumo').innerHTML = `
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-content">
                        <h3>${resumo.total_pedidos}</h3>
                        <p>Total de Pedidos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-content">
                        <h3>R$ ${parseFloat(resumo.valor_total).toLocaleString('pt-BR', {minimumFractionDigits:2})}</h3>
                        <p>Valor Total</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-content">
                        <h3>R$ ${parseFloat(resumo.valor_medio).toLocaleString('pt-BR', {minimumFractionDigits:2})}</h3>
                        <p>Ticket Médio</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-content">
                        <h3>${resumo.pedidos_entregues}</h3>
                        <p>Pedidos Entregues</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-content">
                        <h3>${resumo.pedidos_cancelados}</h3>
                        <p>Pedidos Cancelados</p>
                    </div>
                </div>
            `;
        }
        let chartPedidos, chartProdutos, chartMesas;
        function renderGraficos(data) {
            if (chartPedidos) chartPedidos.destroy();
            if (chartProdutos) chartProdutos.destroy();
            if (chartMesas) chartMesas.destroy();
            chartPedidos = new Chart(document.getElementById('graficoPedidosStatus'), {
                type: 'bar',
                data: {
                    labels: data.pedidos_status.labels,
                    datasets: [{
                        label: 'Pedidos',
                        data: data.pedidos_status.data,
                        backgroundColor: ['#667eea', '#28a745', '#ffc107', '#007bff', '#dc3545']
                    }]
                },
                options: {responsive:true, plugins:{legend:{display:false}}}
            });
            chartProdutos = new Chart(document.getElementById('graficoProdutosVendidos'), {
                type: 'pie',
                data: {
                    labels: data.produtos_mais_vendidos.labels,
                    datasets: [{
                        data: data.produtos_mais_vendidos.data,
                        backgroundColor: ['#667eea', '#28a745', '#ffc107', '#007bff', '#dc3545', '#6f42c1', '#fd7e14']
                    }]
                },
                options: {responsive:true}
            });
            chartMesas = new Chart(document.getElementById('graficoMesasOcupadas'), {
                type: 'doughnut',
                data: {
                    labels: data.mesas_ocupadas.labels,
                    datasets: [{
                        data: data.mesas_ocupadas.data,
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#6c757d']
                    }]
                },
                options: {responsive:true}
            });
        }
        window.onload = carregarRelatorios;
    </script>
</body>
</html>
