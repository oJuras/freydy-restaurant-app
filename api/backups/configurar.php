<?php
/**
 * API para configurar backup automático
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
    
    // Validar dados de entrada
    $config = [
        'ativo' => $input['ativo'] ?? false,
        'frequencia' => $input['frequencia'] ?? 'diario',
        'hora_execucao' => $input['hora_execucao'] ?? '02:00:00',
        'manter_backups' => intval($input['manter_backups'] ?? 10)
    ];
    
    // Validações
    if (!in_array($config['frequencia'], ['diario', 'semanal', 'mensal'])) {
        throw new Exception('Frequência inválida');
    }
    
    if ($config['manter_backups'] < 1 || $config['manter_backups'] > 50) {
        throw new Exception('Quantidade de backups deve estar entre 1 e 50');
    }
    
    // Verifica se usuário tem permissão (apenas admin e gerente)
    if (!in_array($usuario['tipo_usuario'], ['admin', 'gerente'])) {
        throw new Exception('Você não tem permissão para configurar backups automáticos');
    }
    
    $backupModel = new Backup();
    
    // Configurar backup automático
    $resultado = $backupModel->configurarBackupAutomatico($usuario['restaurante_id'], $config);
    
    if ($resultado['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Configuração de backup automático salva com sucesso'
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
