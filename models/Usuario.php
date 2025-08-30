<?php
/**
 * Modelo de Usuário
 * Freydy Restaurant App
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Autentica um usuário
     */
    public function autenticar($email, $senha) {
        $sql = "SELECT u.*, r.nome as nome_restaurante 
                FROM usuarios u 
                INNER JOIN restaurantes r ON u.restaurante_id = r.id 
                WHERE u.email = ? AND u.status = 'ativo'";
        
        $usuario = $this->db->fetch($sql, [$email]);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        
        return false;
    }
    
    /**
     * Busca usuário por ID
     */
    public function buscarPorId($id) {
        $sql = "SELECT u.*, r.nome as nome_restaurante 
                FROM usuarios u 
                INNER JOIN restaurantes r ON u.restaurante_id = r.id 
                WHERE u.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Busca usuário por email e restaurante
     */
    public function buscarPorEmail($email, $restauranteId) {
        $sql = "SELECT * FROM usuarios WHERE email = ? AND restaurante_id = ?";
        return $this->db->fetch($sql, [$email, $restauranteId]);
    }
    
    /**
     * Lista usuários por restaurante
     */
    public function listarPorRestaurante($restauranteId) {
        $sql = "SELECT id, nome, email, tipo_usuario, status, data_cadastro 
                FROM usuarios 
                WHERE restaurante_id = ? 
                ORDER BY nome";
        
        return $this->db->fetchAll($sql, [$restauranteId]);
    }
    
    /**
     * Cria novo usuário
     */
    public function criar($dados) {
        $sql = "INSERT INTO usuarios (restaurante_id, nome, email, senha, tipo_usuario) 
                VALUES (?, ?, ?, ?, ?)";
        
        $senhaHash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        
        $this->db->query($sql, [
            $dados['restaurante_id'],
            $dados['nome'],
            $dados['email'],
            $senhaHash,
            $dados['tipo_usuario']
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Atualiza usuário
     */
    public function atualizar($id, $dados) {
        $sql = "UPDATE usuarios SET 
                nome = ?, 
                email = ?, 
                tipo_usuario = ?, 
                status = ? 
                WHERE id = ?";
        
        $this->db->query($sql, [
            $dados['nome'],
            $dados['email'],
            $dados['tipo_usuario'],
            $dados['status'],
            $id
        ]);
        
        return true;
    }
    
    /**
     * Altera senha do usuário
     */
    public function alterarSenha($id, $novaSenha) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
        $this->db->query($sql, [$senhaHash, $id]);
        
        return true;
    }
    
    /**
     * Verifica se email já existe
     */
    public function emailExiste($email, $excluirId = null) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $params = [$email];
        
        if ($excluirId) {
            $sql .= " AND id != ?";
            $params[] = $excluirId;
        }
        
        $resultado = $this->db->fetch($sql, $params);
        return $resultado !== false;
    }
}
