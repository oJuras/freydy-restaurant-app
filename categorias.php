<?php
/**
 * Gerenciamento de Categorias
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

// Carrega modelos
require_once 'models/Categoria.php';
require_once 'models/Produto.php';

$categoriaModel = new Categoria();
$produtoModel = new Produto();

// Busca categorias do restaurante
$categorias = $categoriaModel->listarPorRestaurante($usuario['restaurante_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Gerenciamento de Categorias</h1>
                <button class="btn btn-primary" onclick="abrirModalNovaCategoria()">
                    <i class="fas fa-plus"></i> Nova Categoria
                </button>
            </div>
            
            <!-- Estatísticas das Categorias -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count($categorias); ?></h3>
                        <p>Total de Categorias</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($categorias, function($c) { return $c['status'] == 'ativo'; })); ?></h3>
                        <p>Categorias Ativas</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo count(array_filter($categorias, function($c) { return $c['status'] == 'inativo'; })); ?></h3>
                        <p>Categorias Inativas</p>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Categorias -->
            <div class="categorias-grid">
                <?php if (empty($categorias)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h3>Nenhuma categoria cadastrada</h3>
                        <p>Comece criando categorias para organizar seus produtos.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categorias as $categoria): ?>
                        <div class="categoria-card status-<?php echo $categoria['status']; ?>">
                            <div class="categoria-header">
                                <h3><?php echo $categoria['nome']; ?></h3>
                                <span class="status-badge status-<?php echo $categoria['status']; ?>">
                                    <?php echo ucfirst($categoria['status']); ?>
                                </span>
                            </div>
                            
                            <div class="categoria-info">
                                <p class="categoria-descricao"><?php echo $categoria['descricao'] ?: 'Sem descrição'; ?></p>
                                <p class="categoria-produtos">
                                    <i class="fas fa-box"></i> 
                                    <?php 
                                    $produtosCategoria = $produtoModel->contarPorCategoria($categoria['id']);
                                    echo $produtosCategoria . ' produto' . ($produtosCategoria != 1 ? 's' : '');
                                    ?>
                                </p>
                                <p class="categoria-data">
                                    <i class="fas fa-calendar"></i> 
                                    Criada em <?php echo date('d/m/Y', strtotime($categoria['data_cadastro'])); ?>
                                </p>
                            </div>
                            
                            <div class="categoria-actions">
                                <button class="btn btn-sm btn-info" onclick="verDetalhesCategoria(<?php echo $categoria['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                
                                <button class="btn btn-sm btn-warning" onclick="editarCategoria(<?php echo $categoria['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                
                                <?php if ($categoria['status'] == 'ativo'): ?>
                                    <button class="btn btn-sm btn-secondary" onclick="alterarStatusCategoria(<?php echo $categoria['id']; ?>, 'inativo')">
                                        <i class="fas fa-pause"></i> Desativar
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success" onclick="alterarStatusCategoria(<?php echo $categoria['id']; ?>, 'ativo')">
                                        <i class="fas fa-play"></i> Ativar
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($produtosCategoria == 0): ?>
                                    <button class="btn btn-sm btn-danger" onclick="excluirCategoria(<?php echo $categoria['id']; ?>)">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-danger" disabled title="Não é possível excluir categoria com produtos">
                                        <i class="fas fa-trash"></i> Excluir
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal de Detalhes da Categoria -->
    <div id="modalCategoria" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="detalhesCategoria"></div>
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script>
        // Dados das categorias para uso nos modais
        let categoriasData = <?php echo json_encode($categorias); ?>;
        
        function verDetalhesCategoria(categoriaId) {
            const categoria = categoriasData.find(c => c.id == categoriaId);
            if (!categoria) return;
            
            const content = `
                <div class="categoria-detalhes">
                    <div class="form-group">
                        <label><strong>Nome:</strong></label>
                        <p>${categoria.nome}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Descrição:</strong></label>
                        <p>${categoria.descricao || 'Sem descrição'}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <span class="status-badge status-${categoria.status}">${categoria.status === 'ativo' ? 'Ativo' : 'Inativo'}</span>
                    </div>
                    <div class="form-group">
                        <label><strong>Data de Criação:</strong></label>
                        <p>${new Date(categoria.data_cadastro).toLocaleDateString('pt-BR')}</p>
                    </div>
                </div>
            `;
            
            modalSystem.open('modalDetalhes', `Detalhes da Categoria`, content);
        }
        
        function abrirModalNovaCategoria() {
            const content = `
                <form id="formNovaCategoria">
                    <div class="form-group">
                        <label for="nome">Nome da Categoria *</label>
                        <input type="text" id="nome" name="nome" required maxlength="50" placeholder="Ex: Pratos Principais">
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" placeholder="Descrição opcional da categoria"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalNovaCategoria', 'Nova Categoria', content, 'salvarNovaCategoria()');
        }
        
        function editarCategoria(categoriaId) {
            const categoria = categoriasData.find(c => c.id == categoriaId);
            if (!categoria) return;
            
            const content = `
                <form id="formEditarCategoria">
                    <input type="hidden" id="categoriaId" value="${categoria.id}">
                    <div class="form-group">
                        <label for="nome">Nome da Categoria *</label>
                        <input type="text" id="nome" name="nome" value="${categoria.nome}" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3">${categoria.descricao || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="ativo" ${categoria.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                            <option value="inativo" ${categoria.status === 'inativo' ? 'selected' : ''}>Inativo</option>
                        </select>
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalEditarCategoria', 'Editar Categoria', content, 'salvarEditarCategoria()');
        }
        
        function alterarStatusCategoria(categoriaId, novoStatus) {
            const acao = novoStatus === 'ativo' ? 'ativar' : 'desativar';
            const categoria = categoriasData.find(c => c.id == categoriaId);
            
            modalSystem.confirm(
                'Alterar Status',
                `Deseja realmente ${acao} a categoria "${categoria.nome}"?`,
                () => {
                    fetch('api/categorias/atualizar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: categoriaId,
                            nome: categoria.nome,
                            descricao: categoria.descricao,
                            status: novoStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao alterar status da categoria');
                    });
                }
            );
        }
        
        function excluirCategoria(categoriaId) {
            const categoria = categoriasData.find(c => c.id == categoriaId);
            
            modalSystem.confirm(
                'Excluir Categoria',
                `Deseja realmente excluir a categoria "${categoria.nome}"? Esta ação não pode ser desfeita.`,
                () => {
                    fetch('api/categorias/excluir.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: categoriaId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Erro: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Erro ao excluir categoria');
                    });
                }
            );
        }
        
        function salvarNovaCategoria() {
            const form = document.getElementById('formNovaCategoria');
            const formData = new FormData(form);
            
            const dados = {
                nome: formData.get('nome'),
                descricao: formData.get('descricao'),
                status: formData.get('status')
            };
            
            fetch('api/categorias/criar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao criar categoria');
            });
        }
        
        function salvarEditarCategoria() {
            const form = document.getElementById('formEditarCategoria');
            const formData = new FormData(form);
            
            const dados = {
                id: formData.get('categoriaId'),
                nome: formData.get('nome'),
                descricao: formData.get('descricao'),
                status: formData.get('status')
            };
            
            fetch('api/categorias/atualizar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dados)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    modalSystem.close();
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao atualizar categoria');
            });
        }
    </script>
</body>
</html>
