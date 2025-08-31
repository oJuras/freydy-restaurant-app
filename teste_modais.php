<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Modais</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div style="padding: 50px;">
        <h1>Teste de Modais</h1>
        <button onclick="testarModalSimples()" class="btn btn-primary">Testar Modal Simples</button>
        <button onclick="testarModalConfirm()" class="btn btn-danger">Testar Modal Confirmação</button>
        <button onclick="testarModalForm()" class="btn btn-success">Testar Modal Formulário</button>
    </div>

    <script src="assets/js/modals.js"></script>
    <script>
        function testarModalSimples() {
            modalSystem.open('teste1', 'Modal Simples', '<p>Este é um modal de teste simples.</p>');
        }

        function testarModalConfirm() {
            modalSystem.confirm(
                'Teste de Confirmação',
                'Deseja realmente executar esta ação?',
                () => {
                    alert('Confirmado!');
                },
                () => {
                    alert('Cancelado!');
                }
            );
        }

        function testarModalForm() {
            const content = `
                <form id="formTeste">
                    <div class="form-group">
                        <label>Nome:</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('teste2', 'Formulário de Teste', content, () => {
                const form = document.getElementById('formTeste');
                const nome = form.nome.value;
                const email = form.email.value;
                alert(`Nome: ${nome}\nEmail: ${email}`);
            });
        }
    </script>
</body>
</html>
