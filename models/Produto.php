<?php
/**
 * Modelo de Produto
 * Freydy Restaurant App
 */

require_once __DIR__ . '/../config/database.php';

class Produto {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Lista todos os produtos de um restaurante
     */
    public function listarPorRestaurante($restauranteId, $categoriaId = null, $status = 'ativo') {
        $sql = "SELECT p.*, c.nome as categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.restaurante_id = ? AND p.status = ?";
        
        $params = [$restauranteId, $status];
        
        if ($categoriaId) {
            $sql .= " AND p.categoria_id = ?";
            $params[] = $categoriaId;
        }
        
        $sql .= " ORDER BY c.nome, p.nome";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Busca produto por ID
     */
    public function buscarPorId($id) {
        $sql = "SELECT p.*, c.nome as categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Cria novo produto
     */
    public function criar($dados) {
        $sql = "INSERT INTO produtos (restaurante_id, categoria_id, nome, descricao, preco, tempo_preparo, imagem_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $dados['restaurante_id'],
            $dados['categoria_id'],
            $dados['nome'],
            $dados['descricao'] ?? '',
            $dados['preco'],
            $dados['tempo_preparo'] ?? 15,
            $dados['imagem_url'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza produto
     */
    public function atualizar($id, $dados) {
        $sql = "UPDATE produtos SET 
                categoria_id = ?, 
                nome = ?, 
                descricao = ?, 
                preco = ?, 
                tempo_preparo = ?, 
                imagem_url = ?,
                data_atualizacao = NOW()
                WHERE id = ?";
        
        $this->db->query($sql, [
            $dados['categoria_id'],
            $dados['nome'],
            $dados['descricao'] ?? '',
            $dados['preco'],
            $dados['tempo_preparo'] ?? 15,
            $dados['imagem_url'] ?? null,
            $id
        ]);
        
        return true;
    }
    
    /**
     * Ativa/desativa produto
     */
    public function alterarStatus($id, $status) {
        $sql = "UPDATE produtos SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $id]);
        
        return true;
    }
    
    /**
     * Remove produto (soft delete)
     */
    public function remover($id) {
        return $this->alterarStatus($id, 'inativo');
    }
    
    /**
     * Busca produtos por categoria
     */
    public function buscarPorCategoria($categoriaId, $restauranteId = null) {
        $sql = "SELECT p.*, c.nome as categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.categoria_id = ? AND p.status = 'ativo'";
        
        $params = [$categoriaId];
        
        if ($restauranteId) {
            $sql .= " AND p.restaurante_id = ?";
            $params[] = $restauranteId;
        }
        
        $sql .= " ORDER BY p.nome";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Busca produtos por nome (busca)
     */
    public function buscarPorNome($nome, $restauranteId) {
        $sql = "SELECT p.*, c.nome as categoria_nome
                FROM produtos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                WHERE p.restaurante_id = ? AND p.status = 'ativo' 
                AND (p.nome LIKE ? OR p.descricao LIKE ?)
                ORDER BY c.nome, p.nome";
        
        $termo = "%{$nome}%";
        
        return $this->db->fetchAll($sql, [$restauranteId, $termo, $termo]);
    }
    
    /**
     * Lista produtos mais vendidos
     */
    public function listarMaisVendidos($restauranteId, $limit = 10, $periodo = 30) {
        $sql = "SELECT p.id, p.nome, p.preco, c.nome as categoria_nome,
                       SUM(ip.quantidade) as total_vendido,
                       COUNT(DISTINCT ip.pedido_id) as total_pedidos
                FROM produtos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                INNER JOIN itens_pedido ip ON p.id = ip.produto_id
                INNER JOIN pedidos ped ON ip.pedido_id = ped.id
                WHERE p.restaurante_id = ? 
                AND ped.status = 'entregue'
                AND ped.data_pedido >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY p.id, p.nome, p.preco, c.nome
                ORDER BY total_vendido DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$restauranteId, $periodo, $limit]);
    }
    
    /**
     * Calcula estatísticas de produtos
     */
    public function calcularEstatisticas($restauranteId) {
        $sql = "SELECT 
                    COUNT(*) as total_produtos,
                    COUNT(CASE WHEN status = 'ativo' THEN 1 END) as produtos_ativos,
                    COUNT(CASE WHEN status = 'inativo' THEN 1 END) as produtos_inativos,
                    AVG(preco) as preco_medio,
                    MIN(preco) as preco_minimo,
                    MAX(preco) as preco_maximo
                FROM produtos 
                WHERE restaurante_id = ?";
        
        return $this->db->fetch($sql, [$restauranteId]);
    }
    
    /**
     * Conta produtos por categoria
     */
    public function contarPorCategoria($categoriaId) {
        $sql = "SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ? AND status = 'ativo'";
        $resultado = $this->db->fetch($sql, [$categoriaId]);
        return $resultado['total'];
    }
}
