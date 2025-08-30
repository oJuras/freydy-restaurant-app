<?php
/**
 * Modelo de Categoria
 * Freydy Restaurant App
 */

require_once __DIR__ . '/../config/database.php';

class Categoria {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lista todas as categorias de um restaurante
     */
    public function listarPorRestaurante($restauranteId) {
        $sql = "SELECT * FROM categorias WHERE restaurante_id = ? ORDER BY nome ASC";
        return $this->db->fetchAll($sql, [$restauranteId]);
    }
    
    /**
     * Lista categorias ativas de um restaurante
     */
    public function listarAtivas($restauranteId) {
        $sql = "SELECT * FROM categorias WHERE restaurante_id = ? AND status = 'ativo' ORDER BY nome ASC";
        return $this->db->fetchAll($sql, [$restauranteId]);
    }
    
    /**
     * Busca uma categoria por ID
     */
    public function buscarPorId($id) {
        $sql = "SELECT * FROM categorias WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Cria uma nova categoria
     */
    public function criar($dados) {
        $sql = "INSERT INTO categorias (restaurante_id, nome, descricao, status) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $dados['restaurante_id'],
            $dados['nome'],
            $dados['descricao'] ?? null,
            $dados['status'] ?? 'ativo'
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza uma categoria
     */
    public function atualizar($id, $dados) {
        $sql = "UPDATE categorias SET nome = ?, descricao = ?, status = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [
            $dados['nome'],
            $dados['descricao'] ?? null,
            $dados['status'] ?? 'ativo',
            $id
        ]);
    }
    
    /**
     * Exclui uma categoria
     */
    public function excluir($id) {
        // Verifica se há produtos nesta categoria
        $sql = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ?";
        $resultado = $this->db->fetch($sql, [$id]);
        
        if ($resultado['total'] > 0) {
            throw new Exception("Não é possível excluir uma categoria que possui produtos.");
        }
        
        $sql = "DELETE FROM categorias WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    /**
     * Atualiza o status de uma categoria
     */
    public function atualizarStatus($id, $status) {
        $sql = "UPDATE categorias SET status = ?, data_atualizacao = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$status, $id]);
    }
    
    /**
     * Verifica se uma categoria existe
     */
    public function existe($id) {
        $sql = "SELECT COUNT(*) as total FROM categorias WHERE id = ?";
        $resultado = $this->db->fetch($sql, [$id]);
        return $resultado['total'] > 0;
    }
    
    /**
     * Verifica se uma categoria pertence ao restaurante
     */
    public function pertenceAoRestaurante($id, $restauranteId) {
        $sql = "SELECT COUNT(*) as total FROM categorias WHERE id = ? AND restaurante_id = ?";
        $resultado = $this->db->fetch($sql, [$id, $restauranteId]);
        return $resultado['total'] > 0;
    }
    
    /**
     * Busca categoria por nome no restaurante
     */
    public function buscarPorNome($nome, $restauranteId) {
        $sql = "SELECT * FROM categorias WHERE nome = ? AND restaurante_id = ?";
        return $this->db->fetch($sql, [$nome, $restauranteId]);
    }
    
    /**
     * Conta produtos por categoria
     */
    public function contarProdutos($categoriaId) {
        $sql = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ?";
        $resultado = $this->db->fetch($sql, [$categoriaId]);
        return $resultado['total'];
    }
    
    /**
     * Lista categorias com contagem de produtos
     */
    public function listarComContagem($restauranteId) {
        $sql = "SELECT c.*, COUNT(p.id) as total_produtos 
                FROM categorias c 
                LEFT JOIN produtos p ON c.id = p.categoria_id 
                WHERE c.restaurante_id = ? 
                GROUP BY c.id 
                ORDER BY c.nome ASC";
        return $this->db->fetchAll($sql, [$restauranteId]);
    }
}
?>
