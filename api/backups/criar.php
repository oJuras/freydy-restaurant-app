<?php
/**
 * API para criar backup manual
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
    $backupModel = new Backup();
    
    // Criar backup completo
    $resultado = $backupModel->criarBackupCompleto($usuario['restaurante_id'], $usuario['id']);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Backup criado com sucesso',
            'backup_id' => $resultado['backup_id'],
            'metadata' => $resultado['metadata']
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
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?>
