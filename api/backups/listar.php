<?php
/**
 * API para listar backups
 */

require_once '../../includes/auth.php';
require_once '../../models/Backup.php';

header('Content-Type: application/json');

// Verifica se usuário está logado
$auth->requerLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $usuario = $auth->getUsuario();
    
    $backupModel = new Backup();
    
    // Lista backups do restaurante
    $backups = $backupModel->listarBackups($usuario['restaurante_id']);
    
    // Calcula estatísticas
    $estatisticas = $backupModel->calcularEstatisticas($usuario['restaurante_id']);
    
    echo json_encode([
        'success' => true,
        'backups' => $backups,
        'estatisticas' => $estatisticas
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
