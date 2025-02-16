<?php
// Inicia a sessão
session_start();

// Defina suas credenciais de conexão com o banco de dados
$servername = "localhost";  // ou o endereço do seu servidor de banco de dados
$username = "root";         // Usuário do banco de dados
$password = "usbw";             // Senha do banco de dados
$dbname = "RestauranteDb";  // Nome do banco de dados

// Crie uma conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifique a conexão
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Defina a variável para exibir mensagem de erro
$error_message = "";

// Verifique se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $restaurantId = $_POST['restaurantId'];
    $password = $_POST['password'];

    // Prepare a consulta SQL
    $sql = "SELECT * FROM restauranteLogin WHERE restauranteId = ? AND senha = ?";
    
    // Prepare a consulta
    $stmt = $conn->prepare($sql);
    
    // Bind os parâmetros
    $stmt->bind_param("is", $restaurantId, $password); // 'i' para inteiro e 's' para string

    // Execute a consulta
    $stmt->execute();
    
    // Armazene o resultado
    $result = $stmt->get_result();

    // Verifique se o restauranteId e senha estão corretos
    if ($result->num_rows > 0) {
        // Login bem-sucedido: Salvar na sessão
        $_SESSION['restaurantId'] = $restaurantId;
        $_SESSION['password'] = $password;

        // Redireciona para a página principal
        header("Location: main.php");
        exit(); // Encerra o script após o redirecionamento
    } else {
        // Login falhou
        $error_message = "ID de restaurante ou senha inválidos!";
    }

    // Feche a consulta
    $stmt->close();
}

// Feche a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(45deg, #8B3A3A, #D2691E); /* Gradiente de vermelho vinho e marrom */
            background-size: 400% 400%;
            animation: gradientAnimation 10s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9); /* Fundo branco com leve transparência */
        }

        h2 {
            margin-bottom: 20px;
            color: #8B3A3A; /* Cor vinho */
        }

        .input-group {
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #D2691E; /* Cor marrom */
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #8B3A3A; /* Cor vinho */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #D2691E; /* Cor marrom para hover */
        }

        .error {
            color: #D2691E; /* Cor marrom */
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Login</h2>
        <form action="" method="POST">
            <div class="input-group">
                <label for="restaurantId">Restaurant ID:</label>
                <input type="text" id="restaurantId" name="restaurantId" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <?php if ($error_message) { ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php } ?>
        </form>
    </div>

</body>
</html>
