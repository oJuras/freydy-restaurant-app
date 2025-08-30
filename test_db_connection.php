<?php
/**
 * Script de teste para diagnóstico da conexão com o banco de dados
 */

echo "<h2>Diagnóstico de Conexão com Banco de Dados</h2>";

// Teste 1: Verificar se o PDO MySQL está disponível
echo "<h3>1. Verificando extensão PDO MySQL:</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "✅ PDO MySQL está disponível<br>";
} else {
    echo "❌ PDO MySQL NÃO está disponível<br>";
    echo "Você precisa instalar a extensão PDO MySQL no PHP<br>";
}

// Teste 2: Tentar conectar sem especificar o banco de dados
echo "<h3>2. Testando conexão com MySQL (sem especificar banco):</h3>";
try {
    $dsn = "mysql:host=localhost;charset=utf8mb4";
    $pdo = new PDO($dsn, 'root', 'usbw');
    echo "✅ Conexão com MySQL bem-sucedida<br>";
    
    // Teste 3: Verificar se o banco de dados existe
    echo "<h3>3. Verificando se o banco 'freydy_restaurant_db' existe:</h3>";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'freydy_restaurant_db'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Banco de dados 'freydy_restaurant_db' existe<br>";
    } else {
        echo "❌ Banco de dados 'freydy_restaurant_db' NÃO existe<br>";
        echo "Você precisa criar o banco de dados primeiro<br>";
    }
    
    // Teste 4: Tentar conectar ao banco específico
    echo "<h3>4. Testando conexão com o banco 'freydy_restaurant_db':</h3>";
    try {
        $dsn_db = "mysql:host=localhost;dbname=freydy_restaurant_db;charset=utf8mb4";
        $pdo_db = new PDO($dsn_db, 'root', 'usbw');
        echo "✅ Conexão com o banco 'freydy_restaurant_db' bem-sucedida<br>";
    } catch (PDOException $e) {
        echo "❌ Erro ao conectar com o banco: " . $e->getMessage() . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro na conexão com MySQL: " . $e->getMessage() . "<br>";
    echo "<br><strong>Soluções possíveis:</strong><br>";
    echo "1. Verifique se o MySQL está rodando<br>";
    echo "2. Verifique se o usuário 'root' existe<br>";
    echo "3. Verifique se a senha 'usbw' está correta<br>";
    echo "4. Verifique se o usuário tem permissões adequadas<br>";
}

// Teste 5: Verificar configurações do PHP
echo "<h3>5. Informações do PHP:</h3>";
echo "Versão do PHP: " . phpversion() . "<br>";
echo "SAPI: " . php_sapi_name() . "<br>";

// Teste 6: Verificar se o arquivo de configuração está correto
echo "<h3>6. Verificando arquivo de configuração:</h3>";
$config_file = 'config/database.php';
if (file_exists($config_file)) {
    echo "✅ Arquivo de configuração existe<br>";
    include_once $config_file;
    echo "✅ Classe Database carregada<br>";
} else {
    echo "❌ Arquivo de configuração não encontrado<br>";
}

echo "<br><h3>Próximos passos:</h3>";
echo "1. Se o MySQL não estiver rodando, inicie o serviço<br>";
echo "2. Se o banco não existir, execute o arquivo database/schema.sql<br>";
echo "3. Se as credenciais estiverem incorretas, atualize config/database.php<br>";
echo "4. Se houver problemas de permissão, configure o usuário MySQL adequadamente<br>";
?>
