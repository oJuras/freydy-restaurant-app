<?php
/**
 * Gerenciamento de Backups
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

// Carrega modelo de backup
require_once 'models/Backup.php';
$backupModel = new Backup();

// Busca backups existentes
$backups = $backupModel->listarBackups($usuario['restaurante_id']);

// Busca configurações de backup automático
$configBackup = $backupModel->buscarConfiguracaoBackup($usuario['restaurante_id']);

// Calcula estatísticas
$totalBackups = count($backups);
$tamanhoTotal = array_sum(array_column($backups, 'tamanho'));
$ultimoBackup = $totalBackups > 0 ? $backups[0]['data_criacao'] : null;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backups - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-database"></i> Gerenciamento de Backups</h1>
                <p>Gerencie os backups do sistema e configure backups automáticos</p>
            </div>
            
            <!-- Cards de Estatísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $totalBackups; ?></h3>
                        <p>Total de Backups</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-hdd"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $this->formatBytes($tamanhoTotal); ?></h3>
                        <p>Espaço Utilizado</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $ultimoBackup ? date('d/m/Y H:i', strtotime($ultimoBackup)) : 'Nunca'; ?></h3>
                        <p>Último Backup</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $configBackup && $configBackup['ativo'] ? 'Ativo' : 'Inativo'; ?></h3>
                        <p>Backup Automático</p>
                    </div>
                </div>
            </div>
            
            <!-- Ações Principais -->
            <div class="actions-section">
                <div class="action-buttons">
                    <button class="btn btn-primary" onclick="criarBackup()">
                        <i class="fas fa-plus"></i> Criar Backup Manual
                    </button>
                    <button class="btn btn-secondary" onclick="abrirModalConfiguracao()">
                        <i class="fas fa-cog"></i> Configurar Backup Automático
                    </button>
                    <button class="btn btn-info" onclick="verificarIntegridade()">
                        <i class="fas fa-shield-alt"></i> Verificar Integridade
                    </button>
                </div>
            </div>
            
            <!-- Lista de Backups -->
            <div class="content-section">
                <div class="section-header">
                    <h2>Backups Disponíveis</h2>
                    <div class="section-actions">
                        <input type="text" id="filtroBackups" placeholder="Filtrar backups..." class="form-control">
                    </div>
                </div>
                
                <?php if (empty($backups)): ?>
                    <div class="empty-state">
                        <i class="fas fa-database"></i>
                        <h3>Nenhum backup encontrado</h3>
                        <p>Crie seu primeiro backup para proteger os dados do sistema.</p>
                        <button class="btn btn-primary" onclick="criarBackup()">
                            <i class="fas fa-plus"></i> Criar Primeiro Backup
                        </button>
                    </div>
                <?php else: ?>
                    <div class="backups-grid" id="backupsGrid">
                        <?php foreach ($backups as $backup): ?>
                            <div class="backup-card" data-backup-id="<?php echo $backup['id']; ?>">
                                <div class="backup-header">
                                    <div class="backup-type">
                                        <i class="fas fa-<?php echo $backup['tipo'] == 'completo' ? 'database' : ($backup['tipo'] == 'banco' ? 'server' : 'folder'); ?>"></i>
                                        <span class="backup-type-label"><?php echo ucfirst($backup['tipo']); ?></span>
                                    </div>
                                    <div class="backup-actions">
                                        <button class="btn-icon" onclick="verDetalhesBackup('<?php echo $backup['id']; ?>')" title="Ver detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-icon" onclick="restaurarBackup('<?php echo $backup['id']; ?>')" title="Restaurar">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button class="btn-icon btn-danger" onclick="excluirBackup('<?php echo $backup['id']; ?>')" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="backup-info">
                                    <div class="backup-details">
                                        <div class="backup-item">
                                            <span class="label">ID:</span>
                                            <span class="value"><?php echo substr($backup['id'], 0, 8) . '...'; ?></span>
                                        </div>
                                        <div class="backup-item">
                                            <span class="label">Data:</span>
                                            <span class="value"><?php echo date('d/m/Y H:i', strtotime($backup['data_criacao'])); ?></span>
                                        </div>
                                        <div class="backup-item">
                                            <span class="label">Tamanho:</span>
                                            <span class="value"><?php echo $this->formatBytes($backup['tamanho']); ?></span>
                                        </div>
                                        <div class="backup-item">
                                            <span class="label">Usuário:</span>
                                            <span class="value"><?php echo htmlspecialchars($backup['nome_usuario'] ?? 'Sistema'); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="backup-status">
                                        <span class="status-badge status-ativo">
                                            <i class="fas fa-check"></i> Disponível
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script>
        // Função para formatar bytes
        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
        
        // Criar backup manual
        function criarBackup() {
            modalSystem.confirm(
                'Criar Backup',
                'Tem certeza que deseja criar um novo backup completo do sistema? Esta operação pode levar alguns minutos.',
                'Criar Backup',
                'Cancelar',
                function() {
                    showNotification('Iniciando criação do backup...', 'info');
                    
                    fetch('api/backups/criar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Backup criado com sucesso!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            showNotification('Erro ao criar backup: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao criar backup', 'error');
                    });
                }
            );
        }
        
        // Ver detalhes do backup
        function verDetalhesBackup(backupId) {
            fetch(`api/backups/detalhes.php?id=${backupId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const backup = data.backup;
                        const metadata = JSON.parse(backup.metadados || '{}');
                        
                        let content = `
                            <div class="backup-details-modal">
                                <div class="detail-section">
                                    <h4>Informações Gerais</h4>
                                    <div class="detail-grid">
                                        <div class="detail-item">
                                            <span class="label">ID:</span>
                                            <span class="value">${backup.id}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Tipo:</span>
                                            <span class="value">${backup.tipo}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Data:</span>
                                            <span class="value">${new Date(backup.data_criacao).toLocaleString('pt-BR')}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Tamanho:</span>
                                            <span class="value">${formatBytes(backup.tamanho)}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-section">
                                    <h4>Conteúdo do Backup</h4>
                                    <div class="backup-content">
                                        <div class="content-item">
                                            <i class="fas fa-database"></i>
                                            <span>Banco de Dados</span>
                                            <span class="content-size">${formatBytes(metadata.database?.size || 0)}</span>
                                        </div>
                                        <div class="content-item">
                                            <i class="fas fa-folder"></i>
                                            <span>Arquivos do Sistema</span>
                                            <span class="content-size">${metadata.files?.length || 0} diretórios</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="detail-section">
                                    <h4>Ações</h4>
                                    <div class="action-buttons">
                                        <button class="btn btn-primary" onclick="restaurarBackup('${backup.id}')">
                                            <i class="fas fa-undo"></i> Restaurar Backup
                                        </button>
                                        <button class="btn btn-secondary" onclick="downloadBackup('${backup.id}')">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        modalSystem.open('modalDetalhes', `Detalhes do Backup`, content);
                    } else {
                        showNotification('Erro ao carregar detalhes do backup', 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showNotification('Erro ao carregar detalhes do backup', 'error');
                });
        }
        
        // Restaurar backup
        function restaurarBackup(backupId) {
            modalSystem.confirm(
                'Restaurar Backup',
                '<strong>ATENÇÃO:</strong> Esta operação irá substituir todos os dados atuais pelos dados do backup. Esta ação não pode ser desfeita. Tem certeza que deseja continuar?',
                'Restaurar Backup',
                'Cancelar',
                function() {
                    showNotification('Iniciando restauração do backup...', 'info');
                    
                    fetch('api/backups/restaurar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            backup_id: backupId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Backup restaurado com sucesso! O sistema será reiniciado.', 'success');
                            setTimeout(() => {
                                window.location.href = 'dashboard.php';
                            }, 3000);
                        } else {
                            showNotification('Erro ao restaurar backup: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao restaurar backup', 'error');
                    });
                }
            );
        }
        
        // Excluir backup
        function excluirBackup(backupId) {
            modalSystem.confirm(
                'Excluir Backup',
                'Tem certeza que deseja excluir este backup? Esta ação não pode ser desfeita.',
                'Excluir',
                'Cancelar',
                function() {
                    fetch('api/backups/excluir.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            backup_id: backupId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Backup excluído com sucesso!', 'success');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification('Erro ao excluir backup: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao excluir backup', 'error');
                    });
                }
            );
        }
        
        // Configurar backup automático
        function abrirModalConfiguracao() {
            const config = <?php echo json_encode($configBackup); ?>;
            
            const content = `
                <form id="formConfigBackup" class="form-grid">
                    <div class="form-group">
                        <label for="ativo">Backup Automático</label>
                        <select id="ativo" name="ativo" class="form-control">
                            <option value="1" ${config?.ativo ? 'selected' : ''}>Ativo</option>
                            <option value="0" ${!config?.ativo ? 'selected' : ''}>Inativo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="frequencia">Frequência</label>
                        <select id="frequencia" name="frequencia" class="form-control">
                            <option value="diario" ${config?.frequencia == 'diario' ? 'selected' : ''}>Diário</option>
                            <option value="semanal" ${config?.frequencia == 'semanal' ? 'selected' : ''}>Semanal</option>
                            <option value="mensal" ${config?.frequencia == 'mensal' ? 'selected' : ''}>Mensal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="hora_execucao">Hora de Execução</label>
                        <input type="time" id="hora_execucao" name="hora_execucao" 
                               value="${config?.hora_execucao || '02:00'}" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="manter_backups">Manter Últimos N Backups</label>
                        <input type="number" id="manter_backups" name="manter_backups" 
                               value="${config?.manter_backups || 10}" min="1" max="50" class="form-control">
                    </div>
                </form>
            `;
            
            modalSystem.openForm(
                'modalConfig',
                'Configurar Backup Automático',
                content,
                'Salvar Configuração',
                'Cancelar',
                function() {
                    const formData = new FormData(document.getElementById('formConfigBackup'));
                    const config = {
                        ativo: formData.get('ativo') === '1',
                        frequencia: formData.get('frequencia'),
                        hora_execucao: formData.get('hora_execucao'),
                        manter_backups: parseInt(formData.get('manter_backups'))
                    };
                    
                    fetch('api/backups/configurar.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(config)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Configuração salva com sucesso!', 'success');
                            modalSystem.close();
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showNotification('Erro ao salvar configuração: ' + data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        showNotification('Erro ao salvar configuração', 'error');
                    });
                }
            );
        }
        
        // Verificar integridade dos backups
        function verificarIntegridade() {
            showNotification('Verificando integridade dos backups...', 'info');
            
            fetch('api/backups/verificar-integridade.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const resultados = data.resultados;
                        let mensagem = `Verificação concluída!\n\n`;
                        
                        resultados.forEach(resultado => {
                            const status = resultado.integrity ? '✅' : '❌';
                            mensagem += `${status} ${resultado.backup_id}: ${resultado.message}\n`;
                        });
                        
                        showNotification(mensagem, 'info');
                    } else {
                        showNotification('Erro na verificação: ' + data.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showNotification('Erro ao verificar integridade', 'error');
                });
        }
        
        // Download de backup
        function downloadBackup(backupId) {
            window.open(`api/backups/download.php?id=${backupId}`, '_blank');
        }
        
        // Filtro de backups
        document.getElementById('filtroBackups').addEventListener('input', function(e) {
            const filtro = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.backup-card');
            
            cards.forEach(card => {
                const texto = card.textContent.toLowerCase();
                card.style.display = texto.includes(filtro) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>

<?php
// Função auxiliar para formatar bytes
function formatBytes($bytes, $decimals = 2) {
    if ($bytes === 0) return '0 Bytes';
    
    $k = 1024;
    $dm = $decimals < 0 ? 0 : $decimals;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
}
?>
