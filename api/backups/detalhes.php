<?php
/**
 * API para buscar detalhes de um backup
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
    $backupId = $_GET['id'] ?? null;
    
    if (!$backupId) {
        throw new Exception('ID do backup não fornecido');
    }
    
    $backupModel = new Backup();
    
    // Buscar backup com informações do usuário
    $sql = "SELECT b.*, u.nome as nome_usuario 
            FROM backups b 
            INNER JOIN usuarios u ON b.usuario_id = u.id 
            WHERE b.id = ? AND b.restaurante_id = ?";
    
    $backup = $backupModel->db->fetch($sql, [$backupId, $usuario['restaurante_id']]);
    
    if (!$backup) {
        throw new Exception('Backup não encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'backup' => $backup
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
