<?php
/**
 * Arquivo de teste para verificar conexão com banco
 */

echo "<h1>Teste de Conexão</h1>";

try {
    require_once 'config/database.php';
    $db = Database::getInstance();
    echo "<p style='color: green;'>✅ Conexão com banco de dados OK!</p>";
    
    // Testar se as tabelas existem
    $tabelas = $db->fetchAll("SHOW TABLES");
    echo "<h2>Tabelas encontradas:</h2>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        $nome = array_values($tabela)[0];
        echo "<li>$nome</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='login.php'>Ir para Login</a></p>";
?>
