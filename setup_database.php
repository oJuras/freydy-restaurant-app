<?php
/**
 * Script para configurar o banco de dados automaticamente
 */

echo "<h2>Configuração Automática do Banco de Dados</h2>";

// Configurações do banco
$host = 'localhost';
$username = 'root';
$password = 'usbw';
$database = 'freydy_restaurant_db';

try {
    // Conectar ao MySQL sem especificar banco
    echo "<h3>1. Conectando ao MySQL...</h3>";
    $dsn = "mysql:host={$host};charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    echo "✅ Conexão com MySQL estabelecida<br>";
    
    // Verificar se o banco existe
    echo "<h3>2. Verificando se o banco '{$database}' existe...</h3>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$database}'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Banco de dados '{$database}' já existe<br>";
    } else {
        echo "❌ Banco de dados '{$database}' não existe. Criando...<br>";
        $pdo->exec("CREATE DATABASE {$database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Banco de dados '{$database}' criado com sucesso<br>";
    }
    
    // Conectar ao banco específico
    echo "<h3>3. Conectando ao banco '{$database}'...</h3>";
    $dsn_db = "mysql:host={$host};dbname={$database};charset=utf8mb4";
    $pdo_db = new PDO($dsn_db, $username, $password);
    echo "✅ Conectado ao banco '{$database}'<br>";
    
    // Verificar se as tabelas existem
    echo "<h3>4. Verificando tabelas...</h3>";
    $tables = ['restaurantes', 'usuarios', 'mesas', 'categorias', 'produtos', 'pedidos', 'itens_pedido', 'historico_pedidos', 'configuracoes'];
    $existing_tables = [];
    
    foreach ($tables as $table) {
        $stmt = $pdo_db->query("SHOW TABLES LIKE '{$table}'");
        if ($stmt->rowCount() > 0) {
            $existing_tables[] = $table;
            echo "✅ Tabela '{$table}' existe<br>";
        } else {
            echo "❌ Tabela '{$table}' não existe<br>";
        }
    }
    
    // Se não existem todas as tabelas, executar o schema
    if (count($existing_tables) < count($tables)) {
        echo "<h3>5. Criando tabelas...</h3>";
        $schema_file = 'database/schema.sql';
        
        if (file_exists($schema_file)) {
            $sql = file_get_contents($schema_file);
            
            // Dividir o SQL em comandos individuais
            $commands = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($commands as $command) {
                if (!empty($command) && !preg_match('/^(USE|CREATE DATABASE)/i', $command)) {
                    try {
                        $pdo_db->exec($command);
                        echo "✅ Comando executado: " . substr($command, 0, 50) . "...<br>";
                    } catch (PDOException $e) {
                        echo "⚠️ Erro no comando: " . $e->getMessage() . "<br>";
                    }
                }
            }
            echo "✅ Schema do banco de dados aplicado<br>";
        } else {
            echo "❌ Arquivo schema.sql não encontrado<br>";
        }
    } else {
        echo "✅ Todas as tabelas já existem<br>";
    }
    
    // Verificar dados de exemplo
    echo "<h3>6. Verificando dados de exemplo...</h3>";
    $stmt = $pdo_db->query("SELECT COUNT(*) as count FROM restaurantes");
    $count = $stmt->fetch()['count'];
    
    if ($count > 0) {
        echo "✅ Dados de exemplo já existem ({$count} restaurantes)<br>";
    } else {
        echo "⚠️ Nenhum restaurante encontrado. Você pode inserir dados manualmente ou executar o schema.sql<br>";
    }
    
    echo "<br><h3>✅ Configuração concluída!</h3>";
    echo "O banco de dados está pronto para uso.<br>";
    echo "<a href='login.php'>Ir para o login</a>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro na configuração:</h3>";
    echo "Erro: " . $e->getMessage() . "<br>";
    echo "<br><strong>Soluções:</strong><br>";
    echo "1. Verifique se o MySQL está rodando<br>";
    echo "2. Verifique se as credenciais estão corretas em config/database.php<br>";
    echo "3. Verifique se o usuário 'root' tem permissões para criar bancos de dados<br>";
}
?>
