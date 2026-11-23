

CREATE DATABASE IF NOT EXISTS pet_shop
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE pet_shop;

-- Usuários: clientes (e_admin = 0) e administradores (e_admin = 1)
CREATE TABLE IF NOT EXISTS usuarios (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    email     VARCHAR(150) NOT NULL UNIQUE,
    senha     VARCHAR(255) NOT NULL,
    e_admin   TINYINT(1)   NOT NULL DEFAULT 0,
    criado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Pets: cada pet pertence a um tutor (usuário)
CREATE TABLE IF NOT EXISTS pets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT          NOT NULL,
    nome       VARCHAR(80)  NOT NULL,
    especie    VARCHAR(40)  NOT NULL,
    raca       VARCHAR(60)  NULL,
    nascimento DATE         NULL,
    criado_em  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pets_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
);

-- Serviços oferecidos (banho, tosa, consulta...)
CREATE TABLE IF NOT EXISTS servicos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nome        VARCHAR(80)   NOT NULL,
    descricao   VARCHAR(255)  NULL,
    preco       DECIMAL(10,2) NOT NULL,
    duracao_min INT           NOT NULL DEFAULT 60,
    ativo       TINYINT(1)    NOT NULL DEFAULT 1
);

-- Agendamentos: um pet marca um serviço em uma data e hora
CREATE TABLE IF NOT EXISTS agendamentos (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    pet_id     INT      NOT NULL,
    servico_id INT      NOT NULL,
    data_hora  DATETIME NOT NULL,
    status     ENUM('pendente','confirmado','cancelado','concluido')
               NOT NULL DEFAULT 'pendente',
    observacao VARCHAR(255) NULL,
    criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_agend_pet
        FOREIGN KEY (pet_id) REFERENCES pets (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_agend_servico
        FOREIGN KEY (servico_id) REFERENCES servicos (id)
);

-- Produtos da loja (opcional)
CREATE TABLE IF NOT EXISTS produtos (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100)  NOT NULL,
    descricao VARCHAR(255)  NULL,
    preco     DECIMAL(10,2) NOT NULL,
    estoque   INT           NOT NULL DEFAULT 0
);

-- Dados de exemplo
INSERT INTO servicos (nome, descricao, preco, duracao_min) VALUES
    ('Banho',            'Banho completo com secagem',      50.00, 60),
    ('Tosa',             'Tosa higiênica ou na máquina',    60.00, 60),
    ('Banho e tosa',     'Combo banho + tosa',              95.00, 120),
    ('Consulta',         'Consulta veterinária de rotina', 120.00, 45),
    ('Vacinação',        'Aplicação de vacina',             80.00, 30);

INSERT INTO produtos (nome, descricao, preco, estoque) VALUES
    ('Ração premium 10kg', 'Ração para cães adultos',  189.90, 20),
    ('Coleira ajustável',  'Coleira de nylon',          29.90, 40),
    ('Shampoo neutro',     'Shampoo 500 ml',            24.90, 30);

    insert into usuarios (nome, email, senha, e_admin) values
    ('nome', 'teste3@gmail.com', '$2y$12$iJvuBFqLxzatZrKZBQF3NeqHqaIgWKdUO1U/WVd.XKpXZL73sOyUy' , 1);
    

