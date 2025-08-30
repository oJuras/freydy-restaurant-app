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
    <title>Usuários - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="page-header">
                <h1>Usuários</h1>
                <button class="btn btn-primary" onclick="abrirModalNovoUsuario()">
                    <i class="fas fa-user-plus"></i> Novo Usuário
                </button>
            </div>
            <div class="filters-section">
                <div class="filter-group">
                    <label for="busca-usuario">Buscar:</label>
                    <input type="text" id="busca-usuario" placeholder="Nome ou e-mail..." onkeyup="filtrarUsuarios()">
                </div>
            </div>
            <div class="usuarios-grid" id="usuariosGrid">
                <div class="no-data">Carregando usuários...</div>
            </div>
        </main>
    </div>
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script>
        let usuariosData = [];
        let usuariosFiltrados = [];
        
        function carregarUsuarios() {
            fetch('api/usuarios/listar.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        usuariosData = data.usuarios;
                        usuariosFiltrados = usuariosData;
                        renderizarUsuarios();
                    } else {
                        document.getElementById('usuariosGrid').innerHTML = '<div class="no-data">Erro ao carregar usuários</div>';
                    }
                });
        }
        
        function renderizarUsuarios() {
            const grid = document.getElementById('usuariosGrid');
            if (!usuariosFiltrados.length) {
                grid.innerHTML = '<div class="no-data">Nenhum usuário encontrado</div>';
                return;
            }
            let html = `<table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>`;
            usuariosFiltrados.forEach(u => {
                html += `<tr>
                    <td>${u.nome}</td>
                    <td>${u.email}</td>
                    <td>${u.tipo_usuario}</td>
                    <td><span class="status-badge status-${u.status}">${u.status}</span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="abrirModalEditarUsuario(${u.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="excluirUsuario(${u.id})"><i class="fas fa-trash"></i></button>
                        <button class="btn btn-sm btn-secondary" onclick="alterarStatusUsuario(${u.id}, '${u.status === 'ativo' ? 'inativo' : 'ativo'}')">
                            <i class="fas fa-${u.status === 'ativo' ? 'pause' : 'play'}"></i>
                        </button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            grid.innerHTML = html;
        }
        
        function filtrarUsuarios() {
            const busca = document.getElementById('busca-usuario').value.toLowerCase();
            usuariosFiltrados = usuariosData.filter(u =>
                u.nome.toLowerCase().includes(busca) ||
                u.email.toLowerCase().includes(busca)
            );
            renderizarUsuarios();
        }
        
        function abrirModalNovoUsuario() {
            const content = `
                <form id="formNovoUsuario">
                    <div class="form-group">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="senha">Senha *</label>
                        <input type="password" id="senha" name="senha" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo *</label>
                        <select id="tipo_usuario" name="tipo_usuario" required>
                            <option value="admin">Administrador</option>
                            <option value="garcom">Garçom</option>
                            <option value="cozinha">Cozinha</option>
                        </select>
                    </div>
                </form>
            `;
            modalSystem.openForm('modalNovoUsuario', 'Novo Usuário', content, 'salvarNovoUsuario()');
        }
        
        function salvarNovoUsuario() {
            const form = document.getElementById('formNovoUsuario');
            const formData = new FormData(form);
            const dados = {
                nome: formData.get('nome'),
                email: formData.get('email'),
                senha: formData.get('senha'),
                tipo_usuario: formData.get('tipo_usuario')
            };
            fetch('api/usuarios/criar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    carregarUsuarios();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        }
        
        function abrirModalEditarUsuario(id) {
            const u = usuariosData.find(x => x.id == id);
            if (!u) return;
            const content = `
                <form id="formEditarUsuario">
                    <input type="hidden" id="usuarioId" value="${u.id}">
                    <div class="form-group">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" value="${u.nome}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail *</label>
                        <input type="email" id="email" name="email" value="${u.email}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="tipo_usuario">Tipo *</label>
                        <select id="tipo_usuario" name="tipo_usuario" required>
                            <option value="admin" ${u.tipo_usuario === 'admin' ? 'selected' : ''}>Administrador</option>
                            <option value="garcom" ${u.tipo_usuario === 'garcom' ? 'selected' : ''}>Garçom</option>
                            <option value="cozinha" ${u.tipo_usuario === 'cozinha' ? 'selected' : ''}>Cozinha</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="ativo" ${u.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                            <option value="inativo" ${u.status === 'inativo' ? 'selected' : ''}>Inativo</option>
                        </select>
                    </div>
                </form>
            `;
            modalSystem.openForm('modalEditarUsuario', 'Editar Usuário', content, 'salvarEditarUsuario()');
        }
        
        function salvarEditarUsuario() {
            const form = document.getElementById('formEditarUsuario');
            const formData = new FormData(form);
            const dados = {
                id: formData.get('usuarioId'),
                nome: formData.get('nome'),
                email: formData.get('email'),
                tipo_usuario: formData.get('tipo_usuario'),
                status: formData.get('status')
            };
            fetch('api/usuarios/atualizar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    carregarUsuarios();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
        }
        
        function alterarStatusUsuario(id, novoStatus) {
            const u = usuariosData.find(x => x.id == id);
            if (!u) return;
            modalSystem.confirm(
                'Alterar Status',
                `Deseja realmente ${novoStatus === 'ativo' ? 'ativar' : 'desativar'} o usuário "${u.nome}"?`,
                () => {
                    fetch('api/usuarios/atualizar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: u.id,
                            nome: u.nome,
                            email: u.email,
                            tipo_usuario: u.tipo_usuario,
                            status: novoStatus
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            carregarUsuarios();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    });
                }
            );
        }
        
        function excluirUsuario(id) {
            const u = usuariosData.find(x => x.id == id);
            if (!u) return;
            modalSystem.confirm(
                'Excluir Usuário',
                `Deseja realmente excluir o usuário "${u.nome}"? Esta ação não pode ser desfeita.`,
                () => {
                    fetch('api/usuarios/excluir.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            carregarUsuarios();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    });
                }
            );
        }
        
        window.onload = carregarUsuarios;
    </script>
</body>
</html>
