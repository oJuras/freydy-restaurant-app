<?php
/**
 * Script de Backup Automático
 * Este script deve ser executado via cron job
 * 
 * Exemplo de cron job:
 * 0 2 * * * /usr/bin/php /path/to/scripts/backup_automatico.php
 */

// Configurações
set_time_limit(0); // Sem limite de tempo
ini_set('memory_limit', '512M'); // Aumentar limite de memória

// Incluir arquivos necessários
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Backup.php';

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    echo "[{$timestamp}] {$message}\n";
    
    // Também salvar em arquivo de log
    $logFile = __DIR__ . '/../logs/backup_automatico.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

try {
    logMessage('Iniciando backup automático...');
    
    $db = Database::getInstance();
    $backupModel = new Backup();
    
    // Buscar configurações de backup automático ativas
    $sql = "SELECT cb.*, r.nome as nome_restaurante 
            FROM configuracoes_backup cb 
            INNER JOIN restaurantes r ON cb.restaurante_id = r.id 
            WHERE cb.ativo = 1";
    
    $configuracoes = $db->fetchAll($sql);
    
    if (empty($configuracoes)) {
        logMessage('Nenhuma configuração de backup automático ativa encontrada.');
        exit(0);
    }
    
    foreach ($configuracoes as $config) {
        try {
            logMessage("Processando backup para restaurante: {$config['nome_restaurante']}");
            
            // Verificar se deve executar backup baseado na frequência
            $deveExecutar = false;
            $ultimoBackup = null;
            
            // Buscar último backup
            $sql = "SELECT data_criacao FROM backups 
                    WHERE restaurante_id = ? 
                    ORDER BY data_criacao DESC 
                    LIMIT 1";
            $ultimoBackup = $db->fetch($sql, [$config['restaurante_id']]);
            
            if (!$ultimoBackup) {
                // Primeiro backup
                $deveExecutar = true;
                logMessage("Primeiro backup para o restaurante.");
            } else {
                $ultimaData = new DateTime($ultimoBackup['data_criacao']);
                $agora = new DateTime();
                $diferenca = $ultimaData->diff($agora);
                
                switch ($config['frequencia']) {
                    case 'diario':
                        $deveExecutar = $diferenca->days >= 1;
                        break;
                    case 'semanal':
                        $deveExecutar = $diferenca->days >= 7;
                        break;
                    case 'mensal':
                        $deveExecutar = $diferenca->days >= 30;
                        break;
                }
            }
            
            if ($deveExecutar) {
                logMessage("Executando backup para restaurante ID: {$config['restaurante_id']}");
                
                // Buscar usuário admin para registrar o backup
                $sql = "SELECT id FROM usuarios 
                        WHERE restaurante_id = ? AND tipo_usuario = 'admin' 
                        ORDER BY id LIMIT 1";
                $admin = $db->fetch($sql, [$config['restaurante_id']]);
                
                if (!$admin) {
                    logMessage("ERRO: Nenhum usuário admin encontrado para restaurante ID: {$config['restaurante_id']}");
                    continue;
                }
                
                // Criar backup
                $resultado = $backupModel->criarBackupCompleto($config['restaurante_id'], $admin['id']);
                
                if ($resultado['success']) {
                    logMessage("Backup criado com sucesso. ID: {$resultado['backup_id']}");
                    
                    // Limpar backups antigos se necessário
                    $sql = "SELECT id FROM backups 
                            WHERE restaurante_id = ? 
                            ORDER BY data_criacao DESC 
                            LIMIT 99999 OFFSET ?";
                    $backupsAntigos = $db->fetchAll($sql, [$config['restaurante_id'], $config['manter_backups']]);
                    
                    foreach ($backupsAntigos as $backupAntigo) {
                        $backupModel->excluirBackup($backupAntigo['id'], $config['restaurante_id']);
                        logMessage("Backup antigo removido. ID: {$backupAntigo['id']}");
                    }
                    
                } else {
                    logMessage("ERRO ao criar backup: {$resultado['error']}");
                }
                
            } else {
                logMessage("Backup não necessário para restaurante ID: {$config['restaurante_id']}");
            }
            
        } catch (Exception $e) {
            logMessage("ERRO ao processar restaurante ID {$config['restaurante_id']}: {$e->getMessage()}");
        }
    }
    
    logMessage('Backup automático concluído com sucesso.');
    
} catch (Exception $e) {
    logMessage("ERRO CRÍTICO: {$e->getMessage()}");
    exit(1);
}

/**
 * Envia notificação por email
 */
function enviarNotificacaoEmail($restauranteId, $resultado) {
    try {
        $db = Database::getInstance();
        
        // Busca dados do restaurante
        $sql = "SELECT nome, email FROM restaurantes WHERE id = ?";
        $restaurante = $db->fetch($sql, [$restauranteId]);
        
        if (!$restaurante || !$restaurante['email']) {
            return;
        }
        
        // Busca usuários admin para notificar
        $sql = "SELECT nome, email FROM usuarios WHERE restaurante_id = ? AND tipo_usuario = 'admin' AND status = 'ativo'";
        $admins = $db->fetchAll($sql, [$restauranteId]);
        
        $para = [];
        foreach ($admins as $admin) {
            if ($admin['email']) {
                $para[] = $admin['email'];
            }
        }
        
        if (empty($para)) {
            return;
        }
        
        $assunto = "Backup Automático - {$restaurante['nome']}";
        $mensagem = "
        <h2>Backup Automático Concluído</h2>
        <p><strong>Restaurante:</strong> {$restaurante['nome']}</p>
        <p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
        <p><strong>Arquivo:</strong> {$resultado['arquivo']}</p>
        <p><strong>Tamanho:</strong> " . formatBytes($resultado['tamanho']) . "</p>
        <p>O backup foi criado com sucesso e está disponível no sistema.</p>
        ";
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: noreply@freydy.com',
            'Reply-To: noreply@freydy.com'
        ];
        
        mail(implode(',', $para), $assunto, $mensagem, implode("\r\n", $headers));
        
        logMessage("Notificação por email enviada para restaurante {$restauranteId}");
        
    } catch (Exception $e) {
        logMessage("Erro ao enviar notificação por email: " . $e->getMessage());
    }
}

/**
 * Formata bytes para exibição
 */
function formatBytes($bytes, $decimals = 2) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $dm = $decimals < 0 ? 0 : $decimals;
    $sizes = array('Bytes', 'KB', 'MB', 'GB', 'TB');
    
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), $dm) . ' ' . $sizes[$i];
}
?>
