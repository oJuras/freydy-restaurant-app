<?php
/**
 * API para configurar backup automático
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';

header('Content-Type: application/json');

// Verifica se usuário está logado
$auth->requerLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $usuario = $auth->getUsuario();
    
    // Verifica se usuário tem permissão (apenas admin)
    if ($usuario['tipo_usuario'] !== 'admin') {
        throw new Exception('Apenas administradores podem configurar backup automático');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validação dos dados
    $frequencia = $input['frequencia'] ?? '';
    $hora = $input['hora'] ?? '';
    $manterBackups = (int)($input['manterBackups'] ?? 10);
    $backupCompleto = isset($input['backupCompleto']) ? 1 : 0;
    $notificarEmail = isset($input['notificarEmail']) ? 1 : 0;
    
    if (!in_array($frequencia, ['diario', 'semanal', 'mensal'])) {
        throw new Exception('Frequência inválida');
    }
    
    if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora)) {
        throw new Exception('Horário inválido');
    }
    
    if ($manterBackups < 1 || $manterBackups > 50) {
        throw new Exception('Número de backups deve estar entre 1 e 50');
    }
    
    $db = Database::getInstance();
    
    // Salva configuração
    $sql = "INSERT INTO configuracoes (restaurante_id, chave, valor, descricao) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE valor = VALUES(valor), data_atualizacao = NOW()";
    
    $configuracoes = [
        ['backup_frequencia', $frequencia, 'Frequência do backup automático'],
        ['backup_hora', $hora, 'Horário do backup automático'],
        ['backup_manter', $manterBackups, 'Número de backups a manter'],
        ['backup_completo', $backupCompleto, 'Se deve fazer backup completo'],
        ['backup_notificar_email', $notificarEmail, 'Se deve notificar por email']
    ];
    
    foreach ($configuracoes as $config) {
        $db->execute($sql, [
            $usuario['restaurante_id'],
            $config[0],
            $config[1],
            $config[2]
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Configuração de backup automático salva com sucesso'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
