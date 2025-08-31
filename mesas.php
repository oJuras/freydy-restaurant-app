<?php
/**
 * Gerenciamento de Mesas
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

// Carrega modelos
require_once 'models/Mesa.php';
require_once 'models/Pedido.php';

$mesaModel = new Mesa();
$pedidoModel = new Pedido();

// Busca mesas do restaurante
$mesas = $mesaModel->listarPorRestaurante($usuario['restaurante_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesas - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Gerenciamento de Mesas</h1>
                <button class="btn btn-primary" onclick="abrirModalNovaMesa()">
                    <i class="fas fa-plus"></i> Nova Mesa
                </button>
            </div>
            
            <!-- Estatísticas das Mesas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($mesas); ?></h3>
                        <p>Total de Mesas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($mesas, function($m) { return $m['status'] == 'livre'; })); ?></h3>
                        <p>Mesas Livres</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($mesas, function($m) { return $m['status'] == 'ocupada'; })); ?></h3>
                        <p>Mesas Ocupadas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($mesas, function($m) { return $m['status'] == 'manutencao'; })); ?></h3>
                        <p>Em Manutenção</p>
                    </div>
                </div>
            </div>
            
            <!-- Layout das Mesas -->
            <div class="mesas-container">
                <h2>Layout do Restaurante</h2>
                <div class="mesas-grid">
                    <?php if (empty($mesas)): ?>
                        <div class="empty-state">
                            <i class="fas fa-table"></i>
                            <h3>Nenhuma mesa cadastrada</h3>
                            <p>Comece adicionando as mesas do seu restaurante.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($mesas as $mesa): ?>
                            <div class="mesa-card status-<?php echo $mesa['status']; ?>" onclick="abrirModalMesa(<?php echo $mesa['id']; ?>)">
                                <div class="mesa-header">
                                    <h3>Mesa <?php echo $mesa['numero']; ?></h3>
                                    <span class="status-badge status-<?php echo $mesa['status']; ?>">
                                        <?php 
                                        switch($mesa['status']) {
                                            case 'livre': echo 'Livre'; break;
                                            case 'ocupada': echo 'Ocupada'; break;
                                            case 'reservada': echo 'Reservada'; break;
                                            case 'manutencao': echo 'Manutenção'; break;
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="mesa-info">
                                    <p><i class="fas fa-users"></i> <?php echo $mesa['capacidade']; ?> pessoas</p>
                                    <?php if ($mesa['status'] == 'ocupada'): ?>
                                        <p><i class="fas fa-clock"></i> Ocupada</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mesa-actions">
                                    <?php if ($mesa['status'] == 'livre'): ?>
                                        <button class="btn btn-sm btn-success" onclick="event.stopPropagation(); ocuparMesa(<?php echo $mesa['id']; ?>)">
                                            <i class="fas fa-user-plus"></i> Ocupar
                                        </button>
                                    <?php elseif ($mesa['status'] == 'ocupada'): ?>
                                        <button class="btn btn-sm btn-warning" onclick="event.stopPropagation(); liberarMesa(<?php echo $mesa['id']; ?>)">
                                            <i class="fas fa-user-minus"></i> Liberar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-sm btn-info" onclick="event.stopPropagation(); editarMesa(<?php echo $mesa['id']; ?>)">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Modal de Mesa -->
    <div id="modalMesa" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="detalhesMesa"></div>
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script>
        // Dados das mesas para uso nos modais
        let mesasData = <?php echo json_encode($mesas); ?>;
        
        function abrirModalMesa(mesaId) {
            const mesa = mesasData.find(m => m.id == mesaId);
            if (!mesa) return;
            
            const content = `
                <div class="mesa-detalhes">
                    <div class="form-group">
                        <label><strong>Número:</strong></label>
                        <p>Mesa ${mesa.numero}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Capacidade:</strong></label>
                        <p>${mesa.capacidade} pessoas</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <span class="status-badge status-${mesa.status}">
                            ${mesa.status === 'livre' ? 'Livre' : 
                              mesa.status === 'ocupada' ? 'Ocupada' : 
                              mesa.status === 'reservada' ? 'Reservada' : 'Manutenção'}
                        </span>
                    </div>
                    <div class="form-group">
                        <label><strong>Posição:</strong></label>
                        <p>X: ${mesa.posicao_x || 0}, Y: ${mesa.posicao_y || 0}</p>
                    </div>
                </div>
            `;
            
            modalSystem.open('modalDetalhes', `Detalhes da Mesa ${mesa.numero}`, content);
        }
        
        function abrirModalNovaMesa() {
            const content = `
                <form id="formNovaMesa">
                    <div class="form-group">
                        <label for="numero">Número da Mesa *</label>
                        <input type="number" id="numero" name="numero" required min="1" placeholder="Ex: 1">
                    </div>
                    <div class="form-group">
                        <label for="capacidade">Capacidade *</label>
                        <input type="number" id="capacidade" name="capacidade" required min="1" max="20" value="4">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="posicao_x">Posição X</label>
                            <input type="number" id="posicao_x" name="posicao_x" value="0" min="0">
                        </div>
                        <div class="form-group">
                            <label for="posicao_y">Posição Y</label>
                            <input type="number" id="posicao_y" name="posicao_y" value="0" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status Inicial</label>
                        <select id="status" name="status">
                            <option value="livre">Livre</option>
                            <option value="manutencao">Manutenção</option>
                        </select>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalNovaMesa', 'Nova Mesa', content, 'salvarNovaMesa()');
        }
        
        function ocuparMesa(mesaId) {
            const mesa = mesasData.find(m => m.id == mesaId);
            
            modalSystem.confirm(
                'Ocupar Mesa',
                `Deseja realmente ocupar a Mesa ${mesa.numero}?`,
                () => {
                    fetch('api/mesas/atualizar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: mesaId,
                            numero: mesa.numero,
                            capacidade: mesa.capacidade,
                            posicao_x: mesa.posicao_x,
                            posicao_y: mesa.posicao_y,
                            status: 'ocupada'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Mesa ocupada com sucesso!', 'success');
                            location.reload();
                        } else {
                            showNotification('Erro: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao ocupar mesa', 'error');
                    });
                }
            );
        }
        
        function liberarMesa(mesaId) {
            const mesa = mesasData.find(m => m.id == mesaId);
            
            modalSystem.confirm(
                'Liberar Mesa',
                `Deseja realmente liberar a Mesa ${mesa.numero}?`,
                () => {
                    fetch('api/mesas/atualizar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: mesaId,
                            numero: mesa.numero,
                            capacidade: mesa.capacidade,
                            posicao_x: mesa.posicao_x,
                            posicao_y: mesa.posicao_y,
                            status: 'livre'
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao liberar mesa');
                    });
                }
            );
        }
        
        function editarMesa(mesaId) {
            const mesa = mesasData.find(m => m.id == mesaId);
            if (!mesa) return;
            
            const content = `
                <form id="formEditarMesa">
                    <input type="hidden" id="mesaId" value="${mesa.id}">
                    <div class="form-group">
                        <label for="numero">Número da Mesa *</label>
                        <input type="number" id="numero" name="numero" value="${mesa.numero}" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="capacidade">Capacidade *</label>
                        <input type="number" id="capacidade" name="capacidade" value="${mesa.capacidade}" required min="1" max="20">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="posicao_x">Posição X</label>
                            <input type="number" id="posicao_x" name="posicao_x" value="${mesa.posicao_x || 0}" min="0">
                        </div>
                        <div class="form-group">
                            <label for="posicao_y">Posição Y</label>
                            <input type="number" id="posicao_y" name="posicao_y" value="${mesa.posicao_y || 0}" min="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="livre" ${mesa.status === 'livre' ? 'selected' : ''}>Livre</option>
                            <option value="ocupada" ${mesa.status === 'ocupada' ? 'selected' : ''}>Ocupada</option>
                            <option value="reservada" ${mesa.status === 'reservada' ? 'selected' : ''}>Reservada</option>
                            <option value="manutencao" ${mesa.status === 'manutencao' ? 'selected' : ''}>Manutenção</option>
                        </select>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalEditarMesa', 'Editar Mesa', content, 'salvarEditarMesa()');
        }
        
        function salvarNovaMesa() {
            const form = document.getElementById('formNovaMesa');
            const formData = new FormData(form);
            
            const dados = {
                numero: formData.get('numero'),
                capacidade: formData.get('capacidade'),
                posicao_x: formData.get('posicao_x'),
                posicao_y: formData.get('posicao_y'),
                status: formData.get('status')
            };
            
            fetch('api/mesas/criar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao criar mesa');
            });
        }
        
        function salvarEditarMesa() {
            const form = document.getElementById('formEditarMesa');
            const formData = new FormData(form);
            
            const dados = {
                id: formData.get('mesaId'),
                numero: formData.get('numero'),
                capacidade: formData.get('capacidade'),
                posicao_x: formData.get('posicao_x'),
                posicao_y: formData.get('posicao_y'),
                status: formData.get('status')
            };
            
            fetch('api/mesas/atualizar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar mesa');
            });
        }
    </script>
</body>
</html>
