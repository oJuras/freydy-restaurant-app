<?php
require_once 'config/database.php';

class Backup {
    private $db;
    private $backupDir;
    private $maxBackups = 10; // Manter apenas os últimos 10 backups
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->backupDir = __DIR__ . '/../backups/';
        
        // Criar diretório de backup se não existir
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Cria backup completo do sistema
     */
    public function criarBackupCompleto($restauranteId, $usuarioId) {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $backupId = uniqid();
            $backupPath = $this->backupDir . "backup_{$timestamp}_{$backupId}";
            
            // Criar diretório do backup
                mkdir($backupPath, 0755, true);
            
            // Backup do banco de dados
            $dbBackup = $this->criarBackupBanco($backupPath, $restauranteId);
            
            // Backup de arquivos
            $filesBackup = $this->criarBackupArquivos($backupPath);
            
            // Criar arquivo de metadados
            $metadata = [
                'backup_id' => $backupId,
                'timestamp' => $timestamp,
                'restaurante_id' => $restauranteId,
                'usuario_id' => $usuarioId,
                'tipo' => 'completo',
                'database' => $dbBackup,
                'files' => $filesBackup,
                'tamanho_total' => $this->calcularTamanhoBackup($backupPath)
            ];
            
            file_put_contents($backupPath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
            
            // Registrar backup no banco
            $this->registrarBackup($backupId, $restauranteId, $usuarioId, 'completo', $metadata);
            
            // Limpar backups antigos
            $this->limparBackupsAntigos();
            
            return [
                'success' => true,
                'backup_id' => $backupId,
                'path' => $backupPath,
                'metadata' => $metadata
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Cria backup apenas do banco de dados
     */
    public function criarBackupBanco($backupPath, $restauranteId) {
        $dbConfig = $this->db->getConfig();
        $dbName = $dbConfig['dbname'];
        $dbUser = $dbConfig['username'];
        $dbPass = $dbConfig['password'];
        $dbHost = $dbConfig['host'];
        
        $sqlFile = $backupPath . '/database.sql';
        
        // Comando mysqldump
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Erro ao criar backup do banco de dados');
        }
        
        return [
            'file' => 'database.sql',
            'size' => filesize($sqlFile),
            'tables' => $this->contarTabelas($dbName)
        ];
    }
    
    /**
     * Cria backup dos arquivos importantes
     */
    public function criarBackupArquivos($backupPath) {
        $filesDir = $backupPath . '/files/';
            mkdir($filesDir, 0755, true);
        
        $filesToBackup = [
            'uploads/' => 'uploads/',
            'config/' => 'config/',
            'assets/' => 'assets/',
            'includes/' => 'includes/'
        ];
        
        $backedUpFiles = [];
        
        foreach ($filesToBackup as $source => $dest) {
            $sourcePath = __DIR__ . '/../' . $source;
            $destPath = $filesDir . $dest;
            
            if (is_dir($sourcePath)) {
                $this->copiarDiretorio($sourcePath, $destPath);
                $backedUpFiles[] = [
                    'path' => $source,
                    'type' => 'directory',
                    'size' => $this->calcularTamanhoDiretorio($sourcePath)
                ];
            }
        }
        
        return $backedUpFiles;
    }
    
    /**
     * Restaura backup completo
     */
    public function restaurarBackup($backupId, $restauranteId, $usuarioId) {
        try {
            $backupInfo = $this->buscarBackup($backupId, $restauranteId);
            
            if (!$backupInfo) {
                throw new Exception('Backup não encontrado');
            }
            
            $backupPath = $backupInfo['caminho'];
            $metadataFile = $backupPath . '/metadata.json';
            
            if (!file_exists($metadataFile)) {
                throw new Exception('Arquivo de metadados não encontrado');
            }
            
            $metadata = json_decode(file_get_contents($metadataFile), true);
            
            // Restaurar banco de dados
            $this->restaurarBanco($backupPath . '/database.sql');
            
            // Restaurar arquivos
            $this->restaurarArquivos($backupPath . '/files/');
            
            // Registrar restauração
            $this->registrarRestauracao($backupId, $restauranteId, $usuarioId);
            
            return [
                'success' => true,
                'message' => 'Backup restaurado com sucesso'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Lista todos os backups disponíveis
     */
    public function listarBackups($restauranteId) {
        $sql = "SELECT * FROM backups WHERE restaurante_id = ? ORDER BY data_criacao DESC";
        return $this->db->fetchAll($sql, [$restauranteId]);
    }
    
    /**
     * Busca informações de um backup específico
     */
    public function buscarBackup($backupId, $restauranteId) {
        $sql = "SELECT * FROM backups WHERE id = ? AND restaurante_id = ?";
        return $this->db->fetch($sql, [$backupId, $restauranteId]);
    }
    
    /**
     * Exclui um backup
     */
    public function excluirBackup($backupId, $restauranteId) {
        try {
            $backup = $this->buscarBackup($backupId, $restauranteId);
            
            if (!$backup) {
                throw new Exception('Backup não encontrado');
            }
            
            // Excluir arquivos
            if (is_dir($backup['caminho'])) {
                $this->removerDiretorio($backup['caminho']);
            }
            
            // Excluir registro do banco
            $sql = "DELETE FROM backups WHERE id = ? AND restaurante_id = ?";
            $this->db->execute($sql, [$backupId, $restauranteId]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Configura backup automático
     */
    public function configurarBackupAutomatico($restauranteId, $config) {
        $sql = "INSERT INTO configuracoes_backup (restaurante_id, frequencia, hora_execucao, manter_backups, ativo) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE 
                frequencia = VALUES(frequencia),
                hora_execucao = VALUES(hora_execucao),
                manter_backups = VALUES(manter_backups),
                ativo = VALUES(ativo)";
        
        $this->db->execute($sql, [
            $restauranteId,
            $config['frequencia'],
            $config['hora_execucao'],
            $config['manter_backups'],
            $config['ativo'] ? 1 : 0
        ]);
        
        return ['success' => true];
    }
    
    /**
     * Busca configuração de backup automático do restaurante
     */
    public function buscarConfiguracaoBackup($restauranteId) {
        $sql = "SELECT * FROM configuracoes_backup WHERE restaurante_id = ?";
        return $this->db->fetch($sql, [$restauranteId]);
    }
    
    // Métodos auxiliares privados
    
    private function registrarBackup($backupId, $restauranteId, $usuarioId, $tipo, $metadata) {
        $sql = "INSERT INTO backups (id, restaurante_id, usuario_id, tipo, caminho, metadados, tamanho) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->execute($sql, [
            $backupId,
            $restauranteId,
            $usuarioId,
            $tipo,
            $metadata['path'],
            json_encode($metadata),
            $metadata['tamanho_total']
        ]);
    }
    
    private function registrarRestauracao($backupId, $restauranteId, $usuarioId) {
        $sql = "INSERT INTO restauracoes_backup (backup_id, restaurante_id, usuario_id) VALUES (?, ?, ?)";
        $this->db->execute($sql, [$backupId, $restauranteId, $usuarioId]);
    }
    
    private function restaurarBanco($sqlFile) {
        $dbConfig = $this->db->getConfig();
        $dbName = $dbConfig['dbname'];
        $dbUser = $dbConfig['username'];
        $dbPass = $dbConfig['password'];
        $dbHost = $dbConfig['host'];
        
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($sqlFile)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception('Erro ao restaurar banco de dados');
        }
    }
    
    private function restaurarArquivos($filesPath) {
        $appRoot = __DIR__ . '/../';
        
        if (is_dir($filesPath)) {
            $this->copiarDiretorio($filesPath, $appRoot);
        }
    }
    
    private function copiarDiretorio($origem, $destino) {
        if (!is_dir($destino)) {
            mkdir($destino, 0755, true);
        }
        
        $dir = opendir($origem);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $origemPath = $origem . '/' . $file;
                $destinoPath = $destino . '/' . $file;
                
                if (is_dir($origemPath)) {
                    $this->copiarDiretorio($origemPath, $destinoPath);
                } else {
                    copy($origemPath, $destinoPath);
                }
            }
        }
        closedir($dir);
    }
    
    private function removerDiretorio($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removerDiretorio($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    
    private function calcularTamanhoBackup($backupPath) {
        return $this->calcularTamanhoDiretorio($backupPath);
    }
    
    private function calcularTamanhoDiretorio($dir) {
        $size = 0;
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            $size += $file->getSize();
        }
        
        return $size;
    }
    
    private function contarTabelas($dbName) {
        $sql = "SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = ?";
        $result = $this->db->fetch($sql, [$dbName]);
        return $result['total'] ?? 0;
    }
    
    private function limparBackupsAntigos() {
        $backups = $this->listarBackups(1); // Assumindo restaurante_id = 1
        
        if (count($backups) > $this->maxBackups) {
            $backupsParaExcluir = array_slice($backups, $this->maxBackups);
            
            foreach ($backupsParaExcluir as $backup) {
                $this->excluirBackup($backup['id'], $backup['restaurante_id']);
            }
        }
    }
}
