<?php
/**
 * Gerenciamento de Produtos
 * Freydy Restaurant App
 */

require_once 'includes/auth.php';

// Verifica se usuário está logado
$auth->requerLogin();

$usuario = $auth->getUsuario();

// Carrega modelos
require_once 'models/Produto.php';
require_once 'models/Categoria.php';

$produtoModel = new Produto();
$categoriaModel = new Categoria();

// Busca produtos e categorias do restaurante
$produtos = $produtoModel->listarPorRestaurante($usuario['restaurante_id']);
$categorias = $categoriaModel->listarPorRestaurante($usuario['restaurante_id']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Freydy Restaurant</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>Gerenciamento de Produtos</h1>
                <button class="btn btn-primary" onclick="abrirModalNovoProduto()">
                    <i class="fas fa-plus"></i> Novo Produto
                </button>
            </div>
            
            <!-- Filtros -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="filtro-categoria">Categoria:</label>
                    <select id="filtro-categoria" onchange="filtrarProdutos()">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filtro-status">Status:</label>
                    <select id="filtro-status" onchange="filtrarProdutos()">
                        <option value="">Todos</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="busca-produto">Buscar:</label>
                    <input type="text" id="busca-produto" placeholder="Nome do produto..." onkeyup="filtrarProdutos()">
                </div>
            </div>
            
            <!-- Lista de Produtos -->
            <div class="produtos-grid">
                <?php if (empty($produtos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-box"></i>
                        <h3>Nenhum produto cadastrado</h3>
                        <p>Comece adicionando produtos ao seu cardápio.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($produtos as $produto): ?>
                        <div class="produto-card" data-categoria="<?php echo $produto['categoria_id']; ?>" data-status="<?php echo $produto['status']; ?>" data-nome="<?php echo strtolower($produto['nome']); ?>">
                            <div class="produto-header">
                                <h3><?php echo $produto['nome']; ?></h3>
                                <span class="status-badge status-<?php echo $produto['status']; ?>">
                                    <?php echo ucfirst($produto['status']); ?>
                                </span>
                            </div>
                            
                            <?php if ($produto['imagem_url']): ?>
                                <div class="produto-imagem">
                                    <img src="<?php echo $produto['imagem_url']; ?>" alt="<?php echo $produto['nome']; ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="produto-info">
                                <p class="produto-descricao"><?php echo $produto['descricao']; ?></p>
                                <p class="produto-categoria">
                                    <i class="fas fa-tag"></i> 
                                    <?php echo $produto['categoria_nome']; ?>
                                </p>
                                <p class="produto-preco">
                                    <strong>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></strong>
                                </p>
                                <p class="produto-tempo">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo $produto['tempo_preparo']; ?> min
                                </p>
                            </div>
                            
                            <div class="produto-actions">
                                <button class="btn btn-sm btn-info" onclick="verDetalhesProduto(<?php echo $produto['id']; ?>)">
                                    <i class="fas fa-eye"></i> Ver
                                </button>
                                
                                <button class="btn btn-sm btn-warning" onclick="editarProduto(<?php echo $produto['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                
                                <?php if ($produto['status'] == 'ativo'): ?>
                                    <button class="btn btn-sm btn-secondary" onclick="alterarStatusProduto(<?php echo $produto['id']; ?>, 'inativo')">
                                        <i class="fas fa-pause"></i> Desativar
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success" onclick="alterarStatusProduto(<?php echo $produto['id']; ?>, 'ativo')">
                                        <i class="fas fa-play"></i> Ativar
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-danger" onclick="excluirProduto(<?php echo $produto['id']; ?>)">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Modal de Detalhes do Produto -->
    <div id="modalProduto" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="detalhesProduto"></div>
        </div>
    </div>
    
    <script src="assets/js/dashboard.js"></script>
    <script src="assets/js/modals.js"></script>
    <script>
        // Dados dos produtos e categorias para uso nos modais
        let produtosData = <?php echo json_encode($produtos); ?>;
        let categoriasData = <?php echo json_encode($categorias); ?>;
        
        function filtrarProdutos() {
            const categoria = document.getElementById('filtro-categoria').value;
            const status = document.getElementById('filtro-status').value;
            const busca = document.getElementById('busca-produto').value.toLowerCase();
            const cards = document.querySelectorAll('.produto-card');
            
            cards.forEach(card => {
                let mostrar = true;
                
                if (categoria && card.dataset.categoria !== categoria) {
                    mostrar = false;
                }
                
                if (status && card.dataset.status !== status) {
                    mostrar = false;
                }
                
                if (busca && !card.dataset.nome.includes(busca)) {
                    mostrar = false;
                }
                
                card.style.display = mostrar ? 'block' : 'none';
            });
        }
        
        function verDetalhesProduto(produtoId) {
            const produto = produtosData.find(p => p.id == produtoId);
            if (!produto) return;
            
            const content = `
                <div class="produto-detalhes">
                    <div class="form-group">
                        <label><strong>Nome:</strong></label>
                        <p>${produto.nome}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Descrição:</strong></label>
                        <p>${produto.descricao || 'Sem descrição'}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Categoria:</strong></label>
                        <p>${produto.categoria_nome}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Preço:</strong></label>
                        <p>R$ ${parseFloat(produto.preco).toFixed(2).replace('.', ',')}</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Tempo de Preparo:</strong></label>
                        <p>${produto.tempo_preparo} minutos</p>
                    </div>
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <span class="status-badge status-${produto.status}">${produto.status === 'ativo' ? 'Ativo' : 'Inativo'}</span>
                    </div>
                    ${produto.imagem_url ? `
                        <div class="form-group">
                            <label><strong>Imagem:</strong></label>
                            <img src="${produto.imagem_url}" alt="${produto.nome}" style="max-width: 200px; border-radius: 8px;">
                        </div>
                    ` : ''}
                </div>
            `;
            
            modalSystem.open('modalDetalhes', `Detalhes do Produto`, content);
        }
        
        function abrirModalNovoProduto() {
            const categoriasOptions = categoriasData.map(cat => 
                `<option value="${cat.id}">${cat.nome}</option>`
            ).join('');
            
            const content = `
                <form id="formNovoProduto">
                    <div class="form-group">
                        <label for="nome">Nome do Produto *</label>
                        <input type="text" id="nome" name="nome" required maxlength="100" placeholder="Ex: Filé Mignon">
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" placeholder="Descrição do produto"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria_id">Categoria *</label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">Selecione uma categoria</option>
                                ${categoriasOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="preco">Preço (R$) *</label>
                            <input type="number" id="preco" name="preco" step="0.01" min="0.01" required placeholder="0,00">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tempo_preparo">Tempo de Preparo (min) *</label>
                            <input type="number" id="tempo_preparo" name="tempo_preparo" min="1" value="15" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="imagem_url">URL da Imagem</label>
                        <input type="url" id="imagem_url" name="imagem_url" placeholder="https://exemplo.com/imagem.jpg">
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalNovoProduto', 'Novo Produto', content, 'salvarNovoProduto()');
        }
        
        function editarProduto(produtoId) {
            const produto = produtosData.find(p => p.id == produtoId);
            if (!produto) return;
            
            const categoriasOptions = categoriasData.map(cat => 
                `<option value="${cat.id}" ${cat.id == produto.categoria_id ? 'selected' : ''}>${cat.nome}</option>`
            ).join('');
            
            const content = `
                <form id="formEditarProduto">
                    <input type="hidden" id="produtoId" value="${produto.id}">
                    <div class="form-group">
                        <label for="nome">Nome do Produto *</label>
                        <input type="text" id="nome" name="nome" value="${produto.nome}" required maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3">${produto.descricao || ''}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria_id">Categoria *</label>
                            <select id="categoria_id" name="categoria_id" required>
                                <option value="">Selecione uma categoria</option>
                                ${categoriasOptions}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="preco">Preço (R$) *</label>
                            <input type="number" id="preco" name="preco" step="0.01" min="0.01" value="${produto.preco}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tempo_preparo">Tempo de Preparo (min) *</label>
                            <input type="number" id="tempo_preparo" name="tempo_preparo" min="1" value="${produto.tempo_preparo}" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="ativo" ${produto.status === 'ativo' ? 'selected' : ''}>Ativo</option>
                                <option value="inativo" ${produto.status === 'inativo' ? 'selected' : ''}>Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="imagem_url">URL da Imagem</label>
                        <input type="url" id="imagem_url" name="imagem_url" value="${produto.imagem_url || ''}" placeholder="https://exemplo.com/imagem.jpg">
                    </div>
                </form>
            `;
            
            modalSystem.openForm('modalEditarProduto', 'Editar Produto', content, 'salvarEditarProduto()');
        }
        
        function alterarStatusProduto(produtoId, novoStatus) {
            const acao = novoStatus === 'ativo' ? 'ativar' : 'desativar';
            const produto = produtosData.find(p => p.id == produtoId);
            
            modalSystem.confirm(
                'Alterar Status',
                `Deseja realmente ${acao} o produto "${produto.nome}"?`,
                () => {
                    fetch('api/produtos/atualizar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id: produtoId,
                            nome: produto.nome,
                            descricao: produto.descricao,
                            categoria_id: produto.categoria_id,
                            preco: produto.preco,
                            tempo_preparo: produto.tempo_preparo,
                            imagem_url: produto.imagem_url,
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
                        alert('Erro ao alterar status do produto');
                    });
                }
            );
        }
        
        function excluirProduto(produtoId) {
            const produto = produtosData.find(p => p.id == produtoId);
            
            modalSystem.confirm(
                'Excluir Produto',
                `Deseja realmente excluir o produto "${produto.nome}"? Esta ação não pode ser desfeita.`,
                () => {
                    fetch('api/produtos/excluir.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: produtoId })
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
                        alert('Erro ao excluir produto');
                    });
                }
            );
        }
        
        function salvarNovoProduto() {
            const form = document.getElementById('formNovoProduto');
            const formData = new FormData(form);
            
            const dados = {
                nome: formData.get('nome'),
                descricao: formData.get('descricao'),
                categoria_id: formData.get('categoria_id'),
                preco: formData.get('preco'),
                tempo_preparo: formData.get('tempo_preparo'),
                imagem_url: formData.get('imagem_url'),
                status: formData.get('status')
            };
            
            fetch('api/produtos/criar.php', {
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
                alert('Erro ao criar produto');
            });
        }
        
        function salvarEditarProduto() {
            const form = document.getElementById('formEditarProduto');
            const formData = new FormData(form);
            
            const dados = {
                id: formData.get('produtoId'),
                nome: formData.get('nome'),
                descricao: formData.get('descricao'),
                categoria_id: formData.get('categoria_id'),
                preco: formData.get('preco'),
                tempo_preparo: formData.get('tempo_preparo'),
                imagem_url: formData.get('imagem_url'),
                status: formData.get('status')
            };
            
            fetch('api/produtos/atualizar.php', {
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
                alert('Erro ao atualizar produto');
            });
        }
    </script>
</body>
</html>
