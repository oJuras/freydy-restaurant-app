<?php
/**
 * API para verificar integridade dos backups
 */

require_once '../../includes/auth.php';
require_once '../../models/Backup.php';

header('Content-Type: application/json');

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $backupModel = new Backup();
    
    // Buscar todos os backups do restaurante
    $backups = $backupModel->listarBackups($usuario['restaurante_id']);
    
    $resultados = [];
    
    foreach ($backups as $backup) {
        $resultado = [
            'backup_id' => $backup['id'],
            'integrity' => true,
            'message' => 'Backup íntegro'
        ];
        
        // Verificar se o diretório do backup existe
        if (!is_dir($backup['caminho'])) {
            $resultado['integrity'] = false;
            $resultado['message'] = 'Diretório do backup não encontrado';
        } else {
            // Verificar se o arquivo de metadados existe
            $metadataFile = $backup['caminho'] . '/metadata.json';
            if (!file_exists($metadataFile)) {
                $resultado['integrity'] = false;
                $resultado['message'] = 'Arquivo de metadados não encontrado';
            } else {
                // Verificar se o arquivo SQL existe
                $sqlFile = $backup['caminho'] . '/database.sql';
                if (!file_exists($sqlFile)) {
                    $resultado['integrity'] = false;
                    $resultado['message'] = 'Arquivo SQL não encontrado';
                } else {
                    // Verificar se o diretório de arquivos existe
                    $filesDir = $backup['caminho'] . '/files/';
                    if (!is_dir($filesDir)) {
                        $resultado['integrity'] = false;
                        $resultado['message'] = 'Diretório de arquivos não encontrado';
                    }
                }
            }
        }
        
        $resultados[] = $resultado;
    }
    
    echo json_encode([
        'success' => true,
        'resultados' => $resultados
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
