<?php
/**
 * API para excluir backup
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
    
    // Verifica se usuário tem permissão (apenas admin e gerente)
    if (!in_array($usuario['tipo_usuario'], ['admin', 'gerente'])) {
        throw new Exception('Você não tem permissão para excluir backups');
    }
    
    $backupModel = new Backup();
    
    // Excluir backup
    $resultado = $backupModel->excluirBackup($backupId, $usuario['restaurante_id']);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Backup excluído com sucesso'
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
