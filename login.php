<?php
session_start();

$servername = "localhost";  
$username = "root";         
$password = "usbw";             
$dbname = "RestauranteDb";  

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $restaurantId = $_POST['restaurantId'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM restauranteLogin WHERE restauranteId = ? AND senha = ?";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("is", $restaurantId, $password); 

    $stmt->execute();
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['restaurantId'] = $restaurantId;
        $_SESSION['password'] = $password;

        header("Location: main.php");
        exit(); 
    } else {
        $error_message = "ID de restaurante ou senha inválidos!";
    }

    $stmt->close();
}

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
            background: linear-gradient(45deg, #8B3A3A, #D2691E); 
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
            background-color: rgba(255, 255, 255, 0.9); 
        }

        h2 {
            margin-bottom: 20px;
            color: #8B3A3A; 
        }

        .input-group {
            margin-bottom: 15px;
        }

        input {
            width: 100%;
            padding: 8px;
            border: 1px solid #D2691E; 
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #8B3A3A;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #D2691E; 
        }

        .error {
            color: #D2691E;
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
