CREATE DATABASE IF NOT EXISTS essenciagirls CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE essenciagirls;

-- 1. Usuários (Login, Recuperação de Senha e Foto em arquivo)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) DEFAULT 'default.png',
    token_recuperacao VARCHAR(100) NULL,
    perfil ENUM('cliente', 'admin') DEFAULT 'cliente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Cadastro Simples 1: Categorias (Ex: Perfumes Florais, Perfumes Amadeirados, Pregadores Acrílico, Presilhas Metal)
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    descricao VARCHAR(255)
);

-- 3. Cadastro Simples 2: Estilos / Notas Olfativas (Ex: Doce, Cítrico, Minimalista, Festa, Dia a Dia)
CREATE TABLE estilos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL
);

-- 4. Produtos (Relação 1..N com categorias)
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    estoque INT NOT NULL DEFAULT 10,
    imagem VARCHAR(255) NOT NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT
);

-- 5. Relação N..N (1): Produto <-> Estilos
CREATE TABLE produto_estilos (
    produto_id INT NOT NULL,
    estilo_id INT NOT NULL,
    PRIMARY KEY (produto_id, estilo_id),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE,
    FOREIGN KEY (estilo_id) REFERENCES estilos(id) ON DELETE CASCADE
);

-- 6. Pedidos (Cabeçalho da compra)
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) DEFAULT 'Confirmado',
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- 7. Relação N..N (2): Carrinho / Itens do Pedido (Pedido <-> Produtos)
CREATE TABLE itens_pedido (
    pedido_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (pedido_id, produto_id),
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id)
);

-- Dados iniciais para testes imediatos
INSERT INTO categorias (nome, descricao) VALUES
('Perfumes Femininos', 'Fragrâncias marcantes e florais'),
('Perfumes Masculinos', 'Notas amadeiradas e especiadas'),
('Pregadores de Cabelo', 'Piranhas e presilhas em acetato e metal');

INSERT INTO estilos (nome) VALUES ('Floral'), ('Amadeirado'), ('Minimalista'), ('Festa'), ('Dia a Dia');

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem) VALUES
(1, 'Eau de Parfum Velours Rose 100ml', 'Notas de rosa damascena, baunilha e almíscar branco.', 189.90, 15, 'perfume_rose.jpg'),
(2, 'Colônia Cedro Noir 100ml', 'Combinação intensa de cedro, pimenta preta e bergamota.', 165.00, 12, 'perfume_cedro.jpg'),
(3, 'Pregador Piranha Acetato Tartaruga', 'Acabamento resistente com mola reforçada para cabelos volumosos.', 29.90, 40, 'pregador_tartaruga.jpg'),
(3, 'Kit 2 Presilhas Pérola Dourada', 'Ideal para penteados presos e eventos sociais.', 34.50, 25, 'presilha_perola.jpg');

INSERT INTO produto_estilos (produto_id, estilo_id) VALUES (1, 1), (1, 4), (2, 2), (3, 3), (3, 5), (4, 4);