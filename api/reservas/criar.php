<?php
require_once '../../includes/auth.php';
require_once '../../models/Reserva.php';

$auth->requerLogin();
$usuario = $auth->getUsuario();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validações
    if (empty($input['nome_cliente'])) {
        throw new Exception('Nome do cliente é obrigatório');
    }
    
    if (empty($input['telefone'])) {
        throw new Exception('Telefone é obrigatório');
    }
    
    if (empty($input['data_reserva'])) {
        throw new Exception('Data da reserva é obrigatória');
    }
    
    if (empty($input['hora_reserva'])) {
        throw new Exception('Horário da reserva é obrigatório');
    }
    
    if (empty($input['numero_pessoas']) || $input['numero_pessoas'] < 1) {
        throw new Exception('Número de pessoas deve ser maior que zero');
    }
    
    if (empty($input['mesa_id'])) {
        throw new Exception('Mesa é obrigatória');
    }
    
    // Validar data (não pode ser no passado)
    $dataReserva = new DateTime($input['data_reserva']);
    $hoje = new DateTime();
    $hoje->setTime(0, 0, 0);
    
    if ($dataReserva < $hoje) {
        throw new Exception('Data da reserva não pode ser no passado');
    }
    
    // Validar email se fornecido
    if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('E-mail inválido');
    }
    
    $reservaModel = new Reserva();
    
    // Verificar se email já existe (se fornecido)
    if (!empty($input['email'])) {
        if ($reservaModel->emailExiste($input['email'], $usuario['restaurante_id'])) {
            throw new Exception('Já existe uma reserva com este e-mail');
        }
    }
    
    // Dados para criar a reserva
    $dados = [
        'restaurante_id' => $usuario['restaurante_id'],
        'nome_cliente' => trim($input['nome_cliente']),
        'telefone' => trim($input['telefone']),
        'email' => !empty($input['email']) ? trim($input['email']) : null,
        'data_reserva' => $input['data_reserva'],
        'hora_reserva' => $input['hora_reserva'],
        'numero_pessoas' => (int)$input['numero_pessoas'],
        'mesa_id' => (int)$input['mesa_id'],
        'observacoes' => !empty($input['observacoes']) ? trim($input['observacoes']) : null,
        'status' => 'pendente'
    ];
    
    $reservaId = $reservaModel->criar($dados);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reserva criada com sucesso',
        'reserva_id' => $reservaId
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
