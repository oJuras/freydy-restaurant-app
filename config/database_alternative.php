<?php
/**
 * Configuração alternativa do banco de dados
 * Freydy Restaurant App
 * 
 * Este arquivo contém configurações alternativas para diferentes ambientes
 */

class DatabaseAlternative {
    private static $instance = null;
    private $connection;
    
    // Configurações alternativas - escolha a que funciona no seu ambiente
    private $configs = [
        'xampp' => [
            'host' => 'localhost',
            'dbname' => 'freydy_restaurant_db',
            'username' => 'root',
            'password' => '', // XAMPP geralmente não tem senha
            'charset' => 'utf8mb4'
        ],
        'wamp' => [
            'host' => 'localhost',
            'dbname' => 'freydy_restaurant_db',
            'username' => 'root',
            'password' => '', // WAMP geralmente não tem senha
            'charset' => 'utf8mb4'
        ],
        'laragon' => [
            'host' => 'localhost',
            'dbname' => 'freydy_restaurant_db',
            'username' => 'root',
            'password' => '', // Laragon geralmente não tem senha
            'charset' => 'utf8mb4'
        ],
        'usbw' => [
            'host' => 'localhost',
            'dbname' => 'freydy_restaurant_db',
            'username' => 'root',
            'password' => 'usbw', // Configuração atual
            'charset' => 'utf8mb4'
        ],
        'custom' => [
            'host' => 'localhost',
            'dbname' => 'freydy_restaurant_db',
            'username' => 'root',
            'password' => 'sua_senha_aqui', // Altere para sua senha
            'charset' => 'utf8mb4'
        ]
    ];
    
    private $current_config = 'usbw'; // Altere para testar diferentes configurações
    
    private function __construct() {
        $config = $this->configs[$this->current_config];
        
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            throw new Exception("Erro na conexão com o banco de dados ({$this->current_config}): " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Erro na execução da query: " . $e->getMessage());
        }
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
    
    // Método para testar diferentes configurações
    public static function testConfig($config_name) {
        $instance = new self();
        $instance->current_config = $config_name;
        
        try {
            $config = $instance->configs[$config_name];
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $pdo = new PDO($dsn, $config['username'], $config['password']);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
