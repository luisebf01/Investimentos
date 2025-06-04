-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS carteira_investimentos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE carteira_investimentos;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de tipos de investimento
CREATE TABLE IF NOT EXISTS tipos_investimento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    descricao TEXT,
    cor VARCHAR(7) DEFAULT '#FFD700'
);

-- Inserir tipos padrão
INSERT INTO tipos_investimento (nome, descricao, cor) VALUES
('Ações', 'Ações de empresas na bolsa de valores', '#00C851'),
('FIIs', 'Fundos de Investimento Imobiliário', '#AA66CC'),
('Renda Fixa', 'CDB, LCI, LCA, Tesouro Direto', '#FF4444'),
('Criptomoedas', 'Bitcoin, Ethereum e outras moedas digitais', '#FF8800'),
('Fundos', 'Fundos de investimento diversos', '#2196F3'),
('Outros', 'Outros tipos de investimento', '#607D8B');

-- Tabela de investimentos
CREATE TABLE IF NOT EXISTS investimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    ticker VARCHAR(20),
    quantidade DECIMAL(15,6) DEFAULT 0,
    preco_medio DECIMAL(15,2) DEFAULT 0,
    valor_investido DECIMAL(15,2) DEFAULT 0,
    valor_atual DECIMAL(15,2) DEFAULT 0,
    rendimento DECIMAL(15,2) DEFAULT 0,
    percentual_rendimento DECIMAL(8,4) DEFAULT 0,
    data_compra DATE,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_id) REFERENCES tipos_investimento(id)
);

-- Tabela de transações
CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    investimento_id INT NOT NULL,
    tipo_transacao ENUM('compra', 'venda') NOT NULL,
    quantidade DECIMAL(15,6) NOT NULL,
    preco DECIMAL(15,2) NOT NULL,
    valor_total DECIMAL(15,2) NOT NULL,
    data_transacao DATE NOT NULL,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (investimento_id) REFERENCES investimentos(id) ON DELETE CASCADE
);

-- Criar usuário admin padrão (senha: admin123)
INSERT INTO usuarios (nome, email, senha) VALUES 
('Administrador', 'admin@carteira.com', '$2y$10$qh9uxPt.xa7P4FMVXIOAUeXuH639DsjTc4LluPcs6GmgJcXPtymxO'); 