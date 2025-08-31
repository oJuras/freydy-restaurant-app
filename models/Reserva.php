<?php
/**
 * Modelo de Reserva
 * Freydy Restaurant App
 */

require_once 'config/database.php';

class Reserva {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lista todas as reservas de um restaurante
     */
    public function listarPorRestaurante($restauranteId, $filtros = []) {
        $sql = "SELECT r.*, m.numero as mesa_numero 
                FROM reservas r 
                INNER JOIN mesas m ON r.mesa_id = m.id 
                WHERE r.restaurante_id = ?";
        
        $params = [$restauranteId];
        
        // Aplicar filtros
        if (!empty($filtros['data'])) {
            $sql .= " AND r.data_reserva = ?";
            $params[] = $filtros['data'];
        }
        
        if (!empty($filtros['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filtros['status'];
        }
        
        if (!empty($filtros['mesa_id'])) {
            $sql .= " AND r.mesa_id = ?";
            $params[] = $filtros['mesa_id'];
        }
        
        $sql .= " ORDER BY r.data_reserva DESC, r.hora_reserva ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Busca reserva por ID
     */
    public function buscarPorId($id, $restauranteId) {
        $sql = "SELECT r.*, m.numero as mesa_numero 
                FROM reservas r 
                INNER JOIN mesas m ON r.mesa_id = m.id 
                WHERE r.id = ? AND r.restaurante_id = ?";
        
        return $this->db->fetch($sql, [$id, $restauranteId]);
    }
    
    /**
     * Cria uma nova reserva
     */
    public function criar($dados) {
        // Verificar se a mesa está disponível
        if (!$this->mesaDisponivel($dados['mesa_id'], $dados['data_reserva'], $dados['hora_reserva'])) {
            throw new Exception('Mesa não está disponível para esta data e horário');
        }
        
        $sql = "INSERT INTO reservas (restaurante_id, mesa_id, nome_cliente, telefone, email, 
                data_reserva, hora_reserva, numero_pessoas, observacoes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $dados['restaurante_id'],
            $dados['mesa_id'],
            $dados['nome_cliente'],
            $dados['telefone'],
            $dados['email'] ?? null,
            $dados['data_reserva'],
            $dados['hora_reserva'],
            $dados['numero_pessoas'],
            $dados['observacoes'] ?? null,
            $dados['status'] ?? 'pendente'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza uma reserva
     */
    public function atualizar($id, $dados, $restauranteId) {
        // Verificar se a reserva existe e pertence ao restaurante
        $reserva = $this->buscarPorId($id, $restauranteId);
        if (!$reserva) {
            throw new Exception('Reserva não encontrada');
        }
        
        // Se mudou mesa, data ou hora, verificar disponibilidade
        if (($dados['mesa_id'] != $reserva['mesa_id'] || 
             $dados['data_reserva'] != $reserva['data_reserva'] || 
             $dados['hora_reserva'] != $reserva['hora_reserva']) &&
            !$this->mesaDisponivel($dados['mesa_id'], $dados['data_reserva'], $dados['hora_reserva'], $id)) {
            throw new Exception('Mesa não está disponível para esta data e horário');
        }
        
        $sql = "UPDATE reservas SET 
                mesa_id = ?, nome_cliente = ?, telefone = ?, email = ?, 
                data_reserva = ?, hora_reserva = ?, numero_pessoas = ?, 
                observacoes = ?, status = ? 
                WHERE id = ? AND restaurante_id = ?";
        
        $this->db->query($sql, [
            $dados['mesa_id'],
            $dados['nome_cliente'],
            $dados['telefone'],
            $dados['email'] ?? null,
            $dados['data_reserva'],
            $dados['hora_reserva'],
            $dados['numero_pessoas'],
            $dados['observacoes'] ?? null,
            $dados['status'],
            $id,
            $restauranteId
        ]);
        
        return true;
    }
    
    /**
     * Exclui uma reserva
     */
    public function excluir($id, $restauranteId) {
        $sql = "DELETE FROM reservas WHERE id = ? AND restaurante_id = ?";
        $this->db->query($sql, [$id, $restauranteId]);
        return true;
    }
    
    /**
     * Atualiza status da reserva
     */
    public function atualizarStatus($id, $status, $restauranteId) {
        $sql = "UPDATE reservas SET status = ? WHERE id = ? AND restaurante_id = ?";
        $this->db->query($sql, [$status, $id, $restauranteId]);
        return true;
    }
    
    /**
     * Verifica se uma mesa está disponível para uma data e horário
     */
    public function mesaDisponivel($mesaId, $data, $hora, $excluirReservaId = null) {
        $sql = "SELECT COUNT(*) as total FROM reservas 
                WHERE mesa_id = ? AND data_reserva = ? AND hora_reserva = ? 
                AND status IN ('confirmada', 'pendente')";
        
        $params = [$mesaId, $data, $hora];
        
        if ($excluirReservaId) {
            $sql .= " AND id != ?";
            $params[] = $excluirReservaId;
        }
        
        $resultado = $this->db->fetch($sql, $params);
        return $resultado['total'] == 0;
    }
    
    /**
     * Busca reservas por data
     */
    public function buscarPorData($restauranteId, $data) {
        $sql = "SELECT r.*, m.numero as mesa_numero 
                FROM reservas r 
                INNER JOIN mesas m ON r.mesa_id = m.id 
                WHERE r.restaurante_id = ? AND r.data_reserva = ? 
                ORDER BY r.hora_reserva ASC";
        
        return $this->db->fetchAll($sql, [$restauranteId, $data]);
    }
    
    /**
     * Calcula estatísticas de reservas
     */
    public function calcularEstatisticas($restauranteId, $dataInicio = null, $dataFim = null) {
        $sql = "SELECT 
                    COUNT(*) as total_reservas,
                    COUNT(CASE WHEN status = 'confirmada' THEN 1 END) as confirmadas,
                    COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
                    COUNT(CASE WHEN status = 'cancelada' THEN 1 END) as canceladas,
                    COUNT(CASE WHEN status = 'concluida' THEN 1 END) as concluidas
                FROM reservas 
                WHERE restaurante_id = ?";
        
        $params = [$restauranteId];
        
        if ($dataInicio) {
            $sql .= " AND data_reserva >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND data_reserva <= ?";
            $params[] = $dataFim;
        }
        
        return $this->db->fetch($sql, $params);
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExiste($email, $restauranteId, $excluirId = null) {
        $sql = "SELECT id FROM reservas WHERE email = ? AND restaurante_id = ?";
        $params = [$email, $restauranteId];
        
        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }
        
        $resultado = $this->db->fetch($sql, $params);
        return $resultado !== false;
    }
}
