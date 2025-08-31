<?php
/**
 * API para download de backup
 */

require_once '../../includes/auth.php';
require_once '../../models/Backup.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $backupId = $_GET['id'] ?? null;
    
    if (!$backupId) {
        throw new Exception('ID do backup não fornecido');
    }
    
    $backupModel = new Backup();
    
    // Buscar backup
    $backup = $backupModel->buscarBackup($backupId, $usuario['restaurante_id']);
    
    if (!$backup) {
        throw new Exception('Backup não encontrado');
    }
    
    $backupPath = $backup['caminho'];
    
    if (!is_dir($backupPath)) {
        throw new Exception('Diretório do backup não encontrado');
    }
    
    // Criar arquivo ZIP temporário
    $tempZip = tempnam(sys_get_temp_dir(), 'backup_');
    $zip = new ZipArchive();
    
    if ($zip->open($tempZip, ZipArchive::CREATE) !== TRUE) {
        throw new Exception('Não foi possível criar o arquivo ZIP');
    }
    
    // Adicionar arquivos ao ZIP
    addToZip($zip, $backupPath, '');
    $zip->close();
    
    // Configurar headers para download
    $filename = 'backup_' . $backupId . '_' . date('Y-m-d_H-i-s') . '.zip';
    
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempZip));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    
    // Enviar arquivo
    readfile($tempZip);
    
    // Limpar arquivo temporário
    unlink($tempZip);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Adiciona arquivos ao ZIP recursivamente
 */
function addToZip($zip, $dir, $basePath) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = $basePath . substr($filePath, strlen($dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
}
?>
