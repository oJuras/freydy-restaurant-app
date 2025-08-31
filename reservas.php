<?php
/**
 * Gerenciamento de Reservas
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

// Carrega modelos
require_once 'models/Reserva.php';
require_once 'models/Mesa.php';

$reservaModel = new Reserva();
$mesaModel = new Mesa();

// Busca reservas do restaurante
$reservas = $reservaModel->listarPorRestaurante($usuario['restaurante_id']);
$mesas = $mesaModel->listarPorRestaurante($usuario['restaurante_id']);

// Busca estatísticas
$estatisticas = $reservaModel->calcularEstatisticas($usuario['restaurante_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Gerenciamento de Reservas</h1>
                <button class="btn btn-primary" onclick="abrirModalNovaReserva()">
                    <i class="fas fa-plus"></i> Nova Reserva
                </button>
            </div>
            
            <!-- Estatísticas das Reservas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $estatisticas['total_reservas']; ?></h3>
                        <p>Total de Reservas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $estatisticas['confirmadas']; ?></h3>
                        <p>Confirmadas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $estatisticas['pendentes']; ?></h3>
                        <p>Pendentes</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $estatisticas['canceladas']; ?></h3>
                        <p>Canceladas</p>
                    </div>
                </div>
            </div>
            
            <!-- Filtros -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="filtro-data">Data:</label>
                    <input type="date" id="filtro-data" onchange="filtrarReservas()">
                </div>
                
                <div class="filter-group">
                    <label for="filtro-status">Status:</label>
                    <select id="filtro-status" onchange="filtrarReservas()">
                        <option value="">Todos</option>
                        <option value="pendente">Pendente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="concluida">Concluída</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filtro-mesa">Mesa:</label>
                    <select id="filtro-mesa" onchange="filtrarReservas()">
                        <option value="">Todas</option>
                        <?php foreach ($mesas as $mesa): ?>
                            <option value="<?php echo $mesa['id']; ?>">Mesa <?php echo $mesa['numero']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Lista de Reservas -->
            <div class="reservas-grid">
                <?php if (empty($reservas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Nenhuma reserva encontrada</h3>
                        <p>Comece criando uma nova reserva.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reservas as $reserva): ?>
                        <div class="reserva-card status-<?php echo $reserva['status']; ?>" 
                             data-data="<?php echo $reserva['data_reserva']; ?>"
                             data-status="<?php echo $reserva['status']; ?>"
                             data-mesa="<?php echo $reserva['mesa_id']; ?>">
                            <div class="reserva-header">
                                <h3><?php echo htmlspecialchars($reserva['nome_cliente']); ?></h3>
                                <span class="status-badge status-<?php echo $reserva['status']; ?>">
                                    <?php echo ucfirst($reserva['status']); ?>
                                </span>
                            </div>
                            
                            <div class="reserva-info">
                                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($reserva['telefone']); ?></p>
                                <?php if ($reserva['email']): ?>
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($reserva['email']); ?></p>
                                <?php endif; ?>
                                <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($reserva['data_reserva'])); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($reserva['hora_reserva'])); ?></p>
                                <p><i class="fas fa-users"></i> <?php echo $reserva['numero_pessoas']; ?> pessoa<?php echo $reserva['numero_pessoas'] != 1 ? 's' : ''; ?></p>
                                <p><i class="fas fa-chair"></i> Mesa <?php echo $reserva['mesa_numero']; ?></p>
                                <?php if ($reserva['observacoes']): ?>
                                    <p><i class="fas fa-comment"></i> <?php echo htmlspecialchars($reserva['observacoes']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="reserva-actions">
                                <button class="btn btn-sm btn-info" onclick="verDetalhesReserva(<?php echo $reserva['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                
                                <button class="btn btn-sm btn-warning" onclick="editarReserva(<?php echo $reserva['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                
                                <?php if ($reserva['status'] == 'pendente'): ?>
                                    <button class="btn btn-sm btn-success" onclick="alterarStatusReserva(<?php echo $reserva['id']; ?>, 'confirmada')">
                                        <i class="fas fa-check"></i> Confirmar
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($reserva['status'] == 'confirmada'): ?>
                                    <button class="btn btn-sm btn-primary" onclick="alterarStatusReserva(<?php echo $reserva['id']; ?>, 'concluida')">
                                        <i class="fas fa-check-double"></i> Concluir
                                    </button>
                                <?php endif; ?>
                                
                                <?php if (in_array($reserva['status'], ['pendente', 'confirmada'])): ?>
                                    <button class="btn btn-sm btn-danger" onclick="alterarStatusReserva(<?php echo $reserva['id']; ?>, 'cancelada')">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-danger" onclick="excluirReserva(<?php echo $reserva['id']; ?>)">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script>
        // Dados das reservas e mesas para uso nos modais
        let reservasData = <?php echo json_encode($reservas); ?>;
        let mesasData = <?php echo json_encode($mesas); ?>;
        
        function filtrarReservas() {
            const data = document.getElementById('filtro-data').value;
            const status = document.getElementById('filtro-status').value;
            const mesa = document.getElementById('filtro-mesa').value;
            const cards = document.querySelectorAll('.reserva-card');
            
            cards.forEach(card => {
                let mostrar = true;
                
                if (data && card.dataset.data !== data) {
                    mostrar = false;
                }
                
                if (status && card.dataset.status !== status) {
                    mostrar = false;
                }
                
                if (mesa && card.dataset.mesa !== mesa) {
                    mostrar = false;
                }
                
                card.style.display = mostrar ? 'block' : 'none';
            });
        }
        
        function verDetalhesReserva(reservaId) {
            const reserva = reservasData.find(r => r.id == reservaId);
            if (!reserva) return;
            
            const content = `
                <div class="reserva-detalhes">
                    <div class="form-group">
                        <label><strong>Cliente:</strong></label>
                        <p>${reserva.nome_cliente}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Telefone:</strong></label>
                        <p>${reserva.telefone}</p>
                    </div>
                    ${reserva.email ? `
                    <div class="form-group">
                        <label><strong>E-mail:</strong></label>
                        <p>${reserva.email}</p>
                    </div>
                    ` : ''}
                    <div class="form-group">
                        <label><strong>Data:</strong></label>
                        <p>${new Date(reserva.data_reserva).toLocaleDateString('pt-BR')}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Horário:</strong></label>
                        <p>${reserva.hora_reserva}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Número de Pessoas:</strong></label>
                        <p>${reserva.numero_pessoas}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Mesa:</strong></label>
                        <p>Mesa ${reserva.mesa_numero}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <span class="status-badge status-${reserva.status}">${reserva.status.charAt(0).toUpperCase() + reserva.status.slice(1)}</span>
                    </div>
                    ${reserva.observacoes ? `
                    <div class="form-group">
                        <label><strong>Observações:</strong></label>
                        <p>${reserva.observacoes}</p>
                    </div>
                    ` : ''}
                    <div class="form-group">
                        <label><strong>Data de Criação:</strong></label>
                        <p>${new Date(reserva.data_criacao).toLocaleDateString('pt-BR')}</p>
                    </div>
                </div>
            `;
            
            modalSystem.open('modalDetalhes', `Detalhes da Reserva`, content);
        }
        
        function abrirModalNovaReserva() {
            let mesasOptions = mesasData.map(m =>
                `<option value="${m.id}">Mesa ${m.numero} (${m.capacidade} pessoas)</option>`
            ).join('');
            
            const content = `
                <form id="formNovaReserva">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome_cliente">Nome do Cliente *</label>
                            <input type="text" id="nome_cliente" name="nome_cliente" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="tel" id="telefone" name="telefone" required maxlength="20">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" maxlength="100">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_reserva">Data da Reserva *</label>
                            <input type="date" id="data_reserva" name="data_reserva" required min="${new Date().toISOString().split('T')[0]}">
                        </div>
                        <div class="form-group">
                            <label for="hora_reserva">Horário *</label>
                            <input type="time" id="hora_reserva" name="hora_reserva" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_pessoas">Número de Pessoas *</label>
                            <input type="number" id="numero_pessoas" name="numero_pessoas" required min="1" max="20" value="2">
                        </div>
                        <div class="form-group">
                            <label for="mesa_id">Mesa *</label>
                            <select id="mesa_id" name="mesa_id" required>
                                <option value="">Selecione a mesa</option>
                                ${mesasOptions}
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea id="observacoes" name="observacoes" rows="3" placeholder="Observações especiais..."></textarea>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalNovaReserva', 'Nova Reserva', content, 'salvarNovaReserva()');
        }
        
        function editarReserva(reservaId) {
            const reserva = reservasData.find(r => r.id == reservaId);
            if (!reserva) return;
            
            let mesasOptions = mesasData.map(m =>
                `<option value="${m.id}" ${m.id == reserva.mesa_id ? 'selected' : ''}>Mesa ${m.numero} (${m.capacidade} pessoas)</option>`
            ).join('');
            
            const content = `
                <form id="formEditarReserva">
                    <input type="hidden" id="reservaId" value="${reserva.id}">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome_cliente">Nome do Cliente *</label>
                            <input type="text" id="nome_cliente" name="nome_cliente" value="${reserva.nome_cliente}" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone *</label>
                            <input type="tel" id="telefone" name="telefone" value="${reserva.telefone}" required maxlength="20">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="${reserva.email || ''}" maxlength="100">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="data_reserva">Data da Reserva *</label>
                            <input type="date" id="data_reserva" name="data_reserva" value="${reserva.data_reserva}" required>
                        </div>
                        <div class="form-group">
                            <label for="hora_reserva">Horário *</label>
                            <input type="time" id="hora_reserva" name="hora_reserva" value="${reserva.hora_reserva}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="numero_pessoas">Número de Pessoas *</label>
                            <input type="number" id="numero_pessoas" name="numero_pessoas" value="${reserva.numero_pessoas}" required min="1" max="20">
                        </div>
                        <div class="form-group">
                            <label for="mesa_id">Mesa *</label>
                            <select id="mesa_id" name="mesa_id" required>
                                <option value="">Selecione a mesa</option>
                                ${mesasOptions}
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="pendente" ${reserva.status === 'pendente' ? 'selected' : ''}>Pendente</option>
                            <option value="confirmada" ${reserva.status === 'confirmada' ? 'selected' : ''}>Confirmada</option>
                            <option value="cancelada" ${reserva.status === 'cancelada' ? 'selected' : ''}>Cancelada</option>
                            <option value="concluida" ${reserva.status === 'concluida' ? 'selected' : ''}>Concluída</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="observacoes">Observações</label>
                        <textarea id="observacoes" name="observacoes" rows="3" placeholder="Observações especiais...">${reserva.observacoes || ''}</textarea>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalEditarReserva', 'Editar Reserva', content, 'salvarEditarReserva()');
        }
        
        function alterarStatusReserva(reservaId, novoStatus) {
            const reserva = reservasData.find(r => r.id == reservaId);
            const statusLabels = {
                'confirmada': 'Confirmar',
                'cancelada': 'Cancelar',
                'concluida': 'Concluir'
            };
            
            modalSystem.confirm(
                'Alterar Status',
                `Deseja realmente ${statusLabels[novoStatus].toLowerCase()} a reserva de "${reserva.nome_cliente}"?`,
                () => {
                    fetch('api/reservas/atualizar-status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: reservaId,
                            status: novoStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification(`Reserva ${statusLabels[novoStatus].toLowerCase()}da com sucesso!`, 'success');
                            location.reload();
                        } else {
                            showNotification('Erro: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao alterar status da reserva', 'error');
                    });
                }
            );
        }
        
        function excluirReserva(reservaId) {
            const reserva = reservasData.find(r => r.id == reservaId);
            
            modalSystem.confirm(
                'Excluir Reserva',
                `Deseja realmente excluir a reserva de "${reserva.nome_cliente}"? Esta ação não pode ser desfeita.`,
                () => {
                    fetch('api/reservas/excluir.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: reservaId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Reserva excluída com sucesso!', 'success');
                            location.reload();
                        } else {
                            showNotification('Erro: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao excluir reserva', 'error');
                    });
                }
            );
        }
        
        function salvarNovaReserva() {
            const form = document.getElementById('formNovaReserva');
            const formData = new FormData(form);
            
            const dados = {
                nome_cliente: formData.get('nome_cliente'),
                telefone: formData.get('telefone'),
                email: formData.get('email'),
                data_reserva: formData.get('data_reserva'),
                hora_reserva: formData.get('hora_reserva'),
                numero_pessoas: parseInt(formData.get('numero_pessoas')),
                mesa_id: parseInt(formData.get('mesa_id')),
                observacoes: formData.get('observacoes')
            };
            
            fetch('api/reservas/criar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    showNotification('Reserva criada com sucesso!', 'success');
                    location.reload();
                } else {
                    showNotification('Erro: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao criar reserva', 'error');
            });
        }
        
        function salvarEditarReserva() {
            const form = document.getElementById('formEditarReserva');
            const formData = new FormData(form);
            
            const dados = {
                id: parseInt(formData.get('reservaId')),
                nome_cliente: formData.get('nome_cliente'),
                telefone: formData.get('telefone'),
                email: formData.get('email'),
                data_reserva: formData.get('data_reserva'),
                hora_reserva: formData.get('hora_reserva'),
                numero_pessoas: parseInt(formData.get('numero_pessoas')),
                mesa_id: parseInt(formData.get('mesa_id')),
                status: formData.get('status'),
                observacoes: formData.get('observacoes')
            };
            
            fetch('api/reservas/atualizar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    showNotification('Reserva atualizada com sucesso!', 'success');
                    location.reload();
                } else {
                    showNotification('Erro: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                showNotification('Erro ao atualizar reserva', 'error');
            });
        }
    </script>
</body>
</html>
