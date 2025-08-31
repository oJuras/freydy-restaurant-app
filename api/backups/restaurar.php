<?php
/**
 * API para restaurar backup
 */

require_once '../../includes/auth.php';
require_once '../../models/Backup.php';

header('Content-Type: application/json');

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $backupId = $input['backup_id'] ?? null;
    
    if (!$backupId) {
        throw new Exception('ID do backup não fornecido');
    }
    
    // Verifica se usuário tem permissão (apenas admin)
    if ($usuario['tipo_usuario'] !== 'admin') {
        throw new Exception('Apenas administradores podem restaurar backups');
    }
    
    $backupModel = new Backup();
    
    // Restaurar backup
    $resultado = $backupModel->restaurarBackup($backupId, $usuario['restaurante_id'], $usuario['id']);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Backup restaurado com sucesso'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $resultado['error']
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
