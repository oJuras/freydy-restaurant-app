<?php
/**
 * Modelo de Mesa
 * Freydy Restaurant App
 */

require_once __DIR__ . '/../config/database.php';

class Mesa {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lista todas as mesas de um restaurante
     */
    public function listarPorRestaurante($restauranteId) {
        $sql = "SELECT * FROM mesas WHERE restaurante_id = ? ORDER BY numero";
        return $this->db->fetchAll($sql, [$restauranteId]);
    }
    
    /**
     * Busca mesa por ID
     */
    public function buscarPorId($id) {
        $sql = "SELECT * FROM mesas WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Busca mesa por número
     */
    public function buscarPorNumero($numero, $restauranteId) {
        $sql = "SELECT * FROM mesas WHERE numero = ? AND restaurante_id = ?";
        return $this->db->fetch($sql, [$numero, $restauranteId]);
    }
    
    /**
     * Cria nova mesa
     */
    public function criar($dados) {
        $sql = "INSERT INTO mesas (restaurante_id, numero, capacidade, posicao_x, posicao_y) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $dados['restaurante_id'],
            $dados['numero'],
            $dados['capacidade'] ?? 4,
            $dados['posicao_x'] ?? null,
            $dados['posicao_y'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza mesa
     */
    public function atualizar($id, $dados) {
        $sql = "UPDATE mesas SET 
                numero = ?, 
                capacidade = ?, 
                status = ?,
                posicao_x = ?,
                posicao_y = ?
                WHERE id = ?";
        
        $this->db->query($sql, [
            $dados['numero'],
            $dados['capacidade'] ?? 4,
            $dados['status'] ?? 'livre',
            $dados['posicao_x'] ?? null,
            $dados['posicao_y'] ?? null,
            $id
        ]);
        
        return true;
    }
    
    /**
     * Atualiza status da mesa
     */
    public function atualizarStatus($id, $status) {
        $sql = "UPDATE mesas SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
        
        return true;
    }
    
    /**
     * Remove mesa
     */
    public function remover($id) {
        $sql = "DELETE FROM mesas WHERE id = ?";
        $this->db->query($sql, [$id]);
        
        return true;
    }
    
    /**
     * Lista mesas por status
     */
    public function listarPorStatus($restauranteId, $status) {
        $sql = "SELECT * FROM mesas WHERE restaurante_id = ? AND status = ? ORDER BY numero";
        return $this->db->fetchAll($sql, [$restauranteId, $status]);
    }
    
    /**
     * Lista mesas livres
     */
    public function listarLivres($restauranteId) {
        return $this->listarPorStatus($restauranteId, 'livre');
    }
    
    /**
     * Lista mesas ocupadas
     */
    public function listarOcupadas($restauranteId) {
        return $this->listarPorStatus($restauranteId, 'ocupada');
    }
    
    /**
     * Verifica se mesa está livre
     */
    public function estaLivre($id) {
        $mesa = $this->buscarPorId($id);
        return $mesa && $mesa['status'] === 'livre';
    }
    
    /**
     * Ocupa mesa
     */
    public function ocupar($id) {
        return $this->atualizarStatus($id, 'ocupada');
    }
    
    /**
     * Libera mesa
     */
    public function liberar($id) {
        return $this->atualizarStatus($id, 'livre');
    }
    
    /**
     * Busca pedidos ativos de uma mesa
     */
    public function buscarPedidosAtivos($mesaId) {
        $sql = "SELECT p.*, u.nome as nome_usuario
                FROM pedidos p
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.mesa_id = ? 
                AND p.status IN ('pendente', 'em_preparo', 'pronto')
                ORDER BY p.data_pedido DESC";
        
        return $this->db->fetchAll($sql, [$mesaId]);
    }
    
    /**
     * Calcula estatísticas de mesas
     */
    public function calcularEstatisticas($restauranteId) {
        $sql = "SELECT 
                    COUNT(*) as total_mesas,
                    COUNT(CASE WHEN status = 'livre' THEN 1 END) as mesas_livres,
                    COUNT(CASE WHEN status = 'ocupada' THEN 1 END) as mesas_ocupadas,
                    COUNT(CASE WHEN status = 'reservada' THEN 1 END) as mesas_reservadas,
                    COUNT(CASE WHEN status = 'manutencao' THEN 1 END) as mesas_manutencao,
                    AVG(capacidade) as capacidade_media
                FROM mesas 
                WHERE restaurante_id = ?";
        
        return $this->db->fetch($sql, [$restauranteId]);
    }
    
    /**
     * Verifica se número de mesa já existe
     */
    public function numeroExiste($numero, $restauranteId, $excluirId = null) {
        $sql = "SELECT id FROM mesas WHERE numero = ? AND restaurante_id = ?";
        $params = [$numero, $restauranteId];
        
        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }
        
        $resultado = $this->db->fetch($sql, $params);
        return $resultado !== false;
    }
}
