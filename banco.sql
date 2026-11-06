CREATE DATABASE IF NOT EXISTS saep_db;
USE saep_db;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL
);

CREATE TABLE Categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT
);

CREATE TABLE Fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cnpj VARCHAR(18) UNIQUE NOT NULL,
    telefone VARCHAR(20)
);

CREATE TABLE Produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    qtd INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    idCategorias INT NOT NULL,
    idFornecedores INT NOT NULL,
    FOREIGN KEY (idCategorias) REFERENCES Categorias(id),
    FOREIGN KEY (idFornecedores) REFERENCES Fornecedores(id)
);

CREATE TABLE Movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('ENTRADA', 'SAIDA') NOT NULL,
    qtd INT NOT NULL,
    dta_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(255) NOT NULL,
    idProdutos INT NOT NULL,
    idusuarios INT NOT NULL,
    FOREIGN KEY (idProdutos) REFERENCES Produtos(id),
    FOREIGN KEY (idusuarios) REFERENCES usuarios(id)
);