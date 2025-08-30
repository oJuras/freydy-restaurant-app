<?php
/**
 * Sistema de Autenticação
 * Freydy Restaurant App
 */

session_start();

require_once __DIR__ . '/../models/Usuario.php';

class Auth {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Verifica se usuário está logado
     */
    public function estaLogado() {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
    
    /**
     * Faz login do usuário
     */
    public function login($email, $senha) {
        $usuario = $this->usuarioModel->autenticar($email, $senha);
        
        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];
            $_SESSION['restaurante_id'] = $usuario['restaurante_id'];
            $_SESSION['restaurante_nome'] = $usuario['nome_restaurante'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Faz logout do usuário
     */
    public function logout() {
        session_destroy();
        return true;
    }
    
    /**
     * Redireciona se não estiver logado
     */
    public function requerLogin() {
        if (!$this->estaLogado()) {
            header("Location: login.php");
            exit();
        }
    }
    
    /**
     * Verifica permissão do usuário
     */
    public function temPermissao($tiposPermitidos) {
        if (!$this->estaLogado()) {
            return false;
        }
        
        if (is_string($tiposPermitidos)) {
            $tiposPermitidos = [$tiposPermitidos];
        }
        
        return in_array($_SESSION['usuario_tipo'], $tiposPermitidos);
    }
    
    /**
     * Redireciona se não tiver permissão
     */
    public function requerPermissao($tiposPermitidos) {
        if (!$this->temPermissao($tiposPermitidos)) {
            header("Location: acesso-negado.php");
            exit();
        }
    }
    
    /**
     * Retorna dados do usuário logado
     */
    public function getUsuario() {
        if (!$this->estaLogado()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'email' => $_SESSION['usuario_email'],
            'tipo' => $_SESSION['usuario_tipo'],
            'restaurante_id' => $_SESSION['restaurante_id'],
            'restaurante_nome' => $_SESSION['restaurante_nome']
        ];
    }
    
    /**
     * Retorna ID do usuário logado
     */
    public function getUsuarioId() {
        return $_SESSION['usuario_id'] ?? null;
    }
    
    /**
     * Retorna ID do restaurante
     */
    public function getRestauranteId() {
        return $_SESSION['restaurante_id'] ?? null;
    }
    
    /**
     * Verifica se é administrador
     */
    public function isAdmin() {
        return $this->temPermissao(['admin']);
    }
    
    /**
     * Verifica se é gerente
     */
    public function isGerente() {
        return $this->temPermissao(['admin', 'gerente']);
    }
    
    /**
     * Verifica se é garçom
     */
    public function isGarcom() {
        return $this->temPermissao(['admin', 'gerente', 'garcom']);
    }
    
    /**
     * Verifica se é cozinheiro
     */
    public function isCozinheiro() {
        return $this->temPermissao(['admin', 'gerente', 'cozinheiro']);
    }
}

// Instância global do sistema de autenticação
$auth = new Auth();
