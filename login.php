<?php
/**
 * Página de Login
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

$error_message = "";

// Comentado temporariamente para evitar loop de redirecionamento
// if ($auth->estaLogado()) {
//     header("Location: dashboard.php");
//     exit();
// }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    if (empty($email) || empty($senha)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        if ($auth->login($email, $senha)) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Email ou senha inválidos!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>Freydy Restaurant</h1>
            <p>Sistema de Gerenciamento</p>
        </div>
        
        <form class="login-form" method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
        </form>
        
        <div class="login-footer">
            <p>Dados de teste:</p>
            <ul>
                <li><strong>Admin:</strong> admin@freydy.com / password</li>
                <li><strong>Garçom:</strong> joao@freydy.com / password</li>
                <li><strong>Cozinheiro:</strong> maria@freydy.com / password</li>
            </ul>
        </div>
    </div>
</body>
</html>
