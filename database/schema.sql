-- Esquema do banco de dados para o sistema de restaurante
-- Criado para o projeto Freydy Restaurant App

-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS freydy_restaurant_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE freydy_restaurant_db;

-- Tabela de restaurantes
CREATE TABLE restaurantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    endereco TEXT,
    telefone VARCHAR(20),
    email VARCHAR(100),
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de usuários do sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('admin', 'gerente', 'garcom', 'cozinheiro') NOT NULL,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
);

-- Tabela de mesas
CREATE TABLE mesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    numero INT NOT NULL,
    capacidade INT DEFAULT 4,
    status ENUM('livre', 'ocupada', 'reservada', 'manutencao') DEFAULT 'livre',
    posicao_x INT,
    posicao_y INT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_mesa_restaurante (restaurante_id, numero)
);

-- Tabela de categorias de produtos
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE
);

-- Tabela de produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    categoria_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    tempo_preparo INT DEFAULT 15, -- em minutos
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    imagem_url VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);

-- Tabela de pedidos
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    mesa_id INT NOT NULL,
    usuario_id INT NOT NULL,
    numero_pedido VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,
    valor_total DECIMAL(10,2) DEFAULT 0.00,
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de itens do pedido
CREATE TABLE itens_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    preco_unitario DECIMAL(10,2) NOT NULL,
    observacoes TEXT,
    status ENUM('pendente', 'em_preparo', 'pronto', 'entregue') DEFAULT 'pendente',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Tabela de histórico de status dos pedidos
CREATE TABLE historico_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    status_anterior ENUM('pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado'),
    status_novo ENUM('pendente', 'em_preparo', 'pronto', 'entregue', 'cancelado') NOT NULL,
    usuario_id INT NOT NULL,
    observacao TEXT,
    data_mudanca TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de configurações do restaurante
CREATE TABLE configuracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    chave VARCHAR(50) NOT NULL,
    valor TEXT,
    descricao TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_config_restaurante (restaurante_id, chave)
);

-- Tabela de reservas
CREATE TABLE IF NOT EXISTS reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    mesa_id INT NOT NULL,
    nome_cliente VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    data_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    numero_pessoas INT NOT NULL,
    observacoes TEXT,
    status ENUM('confirmada', 'pendente', 'cancelada', 'concluida') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    FOREIGN KEY (mesa_id) REFERENCES mesas(id) ON DELETE CASCADE
);

-- Índices para melhor performance
CREATE INDEX idx_reservas_restaurante ON reservas(restaurante_id);
CREATE INDEX idx_reservas_mesa ON reservas(mesa_id);
CREATE INDEX idx_reservas_data ON reservas(data_reserva);
CREATE INDEX idx_reservas_status ON reservas(status);

-- Tabela de histórico de pedidos
CREATE TABLE IF NOT EXISTS historico_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50) NOT NULL,
    observacao TEXT,
    usuario_id INT,
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Índices para histórico
CREATE INDEX idx_historico_pedido ON historico_pedidos(pedido_id);
CREATE INDEX idx_historico_data ON historico_pedidos(data_alteracao);

-- Tabela de backups
CREATE TABLE IF NOT EXISTS backups (
    id VARCHAR(32) PRIMARY KEY,
    restaurante_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo ENUM('completo', 'banco', 'arquivos') NOT NULL,
    caminho VARCHAR(500) NOT NULL,
    metadados JSON,
    tamanho BIGINT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de restaurações de backup
CREATE TABLE IF NOT EXISTS restauracoes_backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_id VARCHAR(32) NOT NULL,
    restaurante_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_restauracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (backup_id) REFERENCES backups(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de configurações de backup automático
CREATE TABLE IF NOT EXISTS configuracoes_backup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurante_id INT NOT NULL,
    frequencia ENUM('diario', 'semanal', 'mensal') DEFAULT 'diario',
    hora_execucao TIME DEFAULT '02:00:00',
    manter_backups INT DEFAULT 10,
    ativo BOOLEAN DEFAULT TRUE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurante_id) REFERENCES restaurantes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_config_restaurante (restaurante_id)
);

-- Índices para backups
CREATE INDEX idx_backups_restaurante ON backups(restaurante_id);
CREATE INDEX idx_backups_data ON backups(data_criacao);
CREATE INDEX idx_restauracoes_backup ON restauracoes_backup(backup_id);
CREATE INDEX idx_restauracoes_data ON restauracoes_backup(data_restauracao);

-- Inserção de dados de exemplo
INSERT INTO restaurantes (nome, cnpj, endereco, telefone, email) VALUES
('Restaurante Freydy', '12.345.678/0001-90', 'Rua das Flores, 123 - Centro', '(11) 99999-9999', 'contato@freydy.com');

INSERT INTO usuarios (restaurante_id, nome, email, senha, tipo_usuario) VALUES
(1, 'Administrador', 'admin@freydy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(1, 'João Silva', 'joao@freydy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'garcom'),
(1, 'Maria Santos', 'maria@freydy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cozinheiro');

INSERT INTO mesas (restaurante_id, numero, capacidade, status) VALUES
(1, 1, 4, 'livre'),
(1, 2, 4, 'livre'),
(1, 3, 6, 'livre'),
(1, 4, 4, 'livre'),
(1, 5, 8, 'livre');

INSERT INTO categorias (restaurante_id, nome, descricao) VALUES
(1, 'Entradas', 'Aperitivos e entradas'),
(1, 'Pratos Principais', 'Pratos principais do cardápio'),
(1, 'Sobremesas', 'Doces e sobremesas'),
(1, 'Bebidas', 'Refrigerantes, sucos e outras bebidas');

INSERT INTO produtos (restaurante_id, categoria_id, nome, descricao, preco, tempo_preparo) VALUES
(1, 1, 'Bruschetta', 'Pão torrado com tomate, manjericão e azeite', 12.90, 8),
(1, 1, 'Carpaccio', 'Carne crua fatiada com azeite e parmesão', 18.90, 10),
(1, 2, 'Penne ao Molho Pesto', 'Massa penne com molho pesto e parmesão', 24.90, 15),
(1, 2, 'Filé Mignon', 'Filé mignon grelhado com batatas', 35.90, 20),
(1, 3, 'Tiramisu', 'Sobremesa italiana tradicional', 15.90, 5),
(1, 3, 'Pudim de Leite', 'Pudim de leite condensado', 12.90, 5),
(1, 4, 'Refrigerante', 'Refrigerante 350ml', 6.90, 2),
(1, 4, 'Suco Natural', 'Suco natural de laranja 300ml', 8.90, 3);

INSERT INTO configuracoes (restaurante_id, chave, valor, descricao) VALUES
(1, 'nome_restaurante', 'Restaurante Freydy', 'Nome do restaurante'),
(1, 'taxa_servico', '10', 'Taxa de serviço em porcentagem'),
(1, 'tempo_estimado_pedido', '20', 'Tempo estimado para preparo em minutos'),
(1, 'aceitar_pedidos', 'true', 'Se o restaurante está aceitando pedidos');
