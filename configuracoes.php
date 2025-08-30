<?php
require_once 'includes/auth.php';
$auth->requerLogin();
$usuario = $auth->getUsuario();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>Configurações</h1>
            </div>
            <div class="dashboard-sections">
                <div class="section-card">
                    <h2>Dados do Restaurante</h2>
                    <form id="formRestaurante">
                        <div class="form-group">
                            <label for="nome_restaurante">Nome</label>
                            <input type="text" id="nome_restaurante" name="nome_restaurante" value="<?php echo htmlspecialchars($usuario['nome_restaurante']); ?>" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="email_restaurante">E-mail</label>
                            <input type="email" id="email_restaurante" name="email_restaurante" value="<?php echo htmlspecialchars($usuario['email_restaurante'] ?? ''); ?>" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>" maxlength="20">
                        </div>
                        <div class="form-group">
                            <label for="endereco">Endereço</label>
                            <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($usuario['endereco'] ?? ''); ?>" maxlength="200">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="salvarRestaurante()"><i class="fas fa-save"></i> Salvar</button>
                    </form>
                </div>
                <div class="section-card">
                    <h2>Alterar Senha</h2>
                    <form id="formSenha">
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual</label>
                            <input type="password" id="senha_atual" name="senha_atual" required>
                        </div>
                        <div class="form-group">
                            <label for="nova_senha">Nova Senha</label>
                            <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                        </div>
                        <button type="button" class="btn btn-primary" onclick="alterarSenha()"><i class="fas fa-key"></i> Alterar Senha</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="assets/js/dashboard.js"></script>
    <script>
        function salvarRestaurante() {
            const form = document.getElementById('formRestaurante');
            const formData = new FormData(form);
            fetch('api/configuracoes/salvar_restaurante.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Dados do restaurante salvos com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        }
        function alterarSenha() {
            const form = document.getElementById('formSenha');
            const formData = new FormData(form);
            fetch('api/configuracoes/alterar_senha.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Senha alterada com sucesso!');
                    form.reset();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>
