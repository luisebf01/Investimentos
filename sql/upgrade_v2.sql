-- UPGRADE PARA VERSÃO 2.0 DA CARTEIRA LUXO
-- Execute este script para adicionar as novas funcionalidades

USE carteira_investimentos;

-- Tabela para histórico da carteira (snapshots diários)
CREATE TABLE IF NOT EXISTS historico_carteira (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_snapshot DATE NOT NULL,
    total_investido DECIMAL(15,2) DEFAULT 0,
    total_atual DECIMAL(15,2) DEFAULT 0,
    total_rendimento DECIMAL(15,2) DEFAULT 0,
    percentual_rendimento DECIMAL(8,4) DEFAULT 0,
    quantidade_investimentos INT DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (usuario_id, data_snapshot)
);

-- Tabela para histórico detalhado por investimento
CREATE TABLE IF NOT EXISTS historico_investimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    investimento_id INT NOT NULL,
    usuario_id INT NOT NULL,
    data_snapshot DATE NOT NULL,
    valor_atual DECIMAL(15,2) DEFAULT 0,
    rendimento DECIMAL(15,2) DEFAULT 0,
    percentual_rendimento DECIMAL(8,4) DEFAULT 0,
    preco_unitario DECIMAL(15,2) DEFAULT 0,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (investimento_id) REFERENCES investimentos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_investment_date (investimento_id, data_snapshot)
);

-- Tabela de metas de investimento
CREATE TABLE IF NOT EXISTS metas_investimento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(100) NOT NULL,
    descricao TEXT,
    valor_meta DECIMAL(15,2) NOT NULL,
    valor_atual DECIMAL(15,2) DEFAULT 0,
    data_limite DATE,
    tipo_meta ENUM('valor_total', 'valor_mensal', 'percentual_rendimento') DEFAULT 'valor_total',
    status ENUM('ativo', 'concluido', 'pausado') DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de alertas personalizados
CREATE TABLE IF NOT EXISTS alertas_investimento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    investimento_id INT,
    tipo_alerta ENUM('preco_subiu', 'preco_desceu', 'percentual_ganho', 'percentual_perda') NOT NULL,
    valor_referencia DECIMAL(15,2) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    notificado BOOLEAN DEFAULT FALSE,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_notificacao TIMESTAMP NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (investimento_id) REFERENCES investimentos(id) ON DELETE CASCADE
);

-- Tabela de configurações do usuário
CREATE TABLE IF NOT EXISTS configuracoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tema ENUM('dark', 'light') DEFAULT 'dark',
    email_notificacoes BOOLEAN DEFAULT TRUE,
    auto_update_precos BOOLEAN DEFAULT TRUE,
    frequencia_backup ENUM('diario', 'semanal', 'mensal') DEFAULT 'semanal',
    moeda_padrao ENUM('BRL', 'USD', 'EUR') DEFAULT 'BRL',
    timezone VARCHAR(50) DEFAULT 'America/Sao_Paulo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_config (usuario_id)
);

-- Adicionar colunas extras na tabela investimentos (se não existirem)
ALTER TABLE investimentos 
ADD COLUMN IF NOT EXISTS preco_atual DECIMAL(15,2) DEFAULT 0 COMMENT 'Preço unitário atual do ativo',
ADD COLUMN IF NOT EXISTS ultima_atualizacao_preco TIMESTAMP NULL COMMENT 'Última vez que o preço foi atualizado',
ADD COLUMN IF NOT EXISTS meta_preco DECIMAL(15,2) DEFAULT 0 COMMENT 'Preço alvo definido pelo usuário',
ADD COLUMN IF NOT EXISTS stop_loss DECIMAL(15,2) DEFAULT 0 COMMENT 'Preço de stop loss',
ADD COLUMN IF NOT EXISTS take_profit DECIMAL(15,2) DEFAULT 0 COMMENT 'Preço de take profit';

-- Adicionar índices para melhor performance
CREATE INDEX IF NOT EXISTS idx_historico_carteira_data ON historico_carteira(usuario_id, data_snapshot);
CREATE INDEX IF NOT EXISTS idx_historico_investimentos_data ON historico_investimentos(investimento_id, data_snapshot);
CREATE INDEX IF NOT EXISTS idx_alertas_ativo ON alertas_investimento(usuario_id, ativo);
CREATE INDEX IF NOT EXISTS idx_investimentos_ticker ON investimentos(ticker);
CREATE INDEX IF NOT EXISTS idx_investimentos_usuario_tipo ON investimentos(usuario_id, tipo_id);

-- Inserir configurações padrão para usuários existentes
INSERT IGNORE INTO configuracoes_usuario (usuario_id)
SELECT id FROM usuarios;

-- Inserir algumas metas padrão para o usuário admin (exemplo)
INSERT IGNORE INTO metas_investimento (usuario_id, titulo, descricao, valor_meta, tipo_meta)
SELECT 
    u.id,
    'Primeira Meta - R$ 10.000',
    'Alcançar R$ 10.000 em investimentos',
    10000.00,
    'valor_total'
FROM usuarios u 
WHERE u.email = 'admin@carteira.com';

-- Procedure para criar snapshot diário da carteira
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS CriarSnapshotCarteira(IN p_usuario_id INT)
BEGIN
    DECLARE v_total_investido DECIMAL(15,2) DEFAULT 0;
    DECLARE v_total_atual DECIMAL(15,2) DEFAULT 0;
    DECLARE v_total_rendimento DECIMAL(15,2) DEFAULT 0;
    DECLARE v_percentual DECIMAL(8,4) DEFAULT 0;
    DECLARE v_quantidade INT DEFAULT 0;
    DECLARE v_hoje DATE DEFAULT CURDATE();
    
    -- Calcular totais atuais
    SELECT 
        COALESCE(SUM(valor_investido), 0),
        COALESCE(SUM(valor_atual), 0),
        COALESCE(SUM(rendimento), 0),
        COUNT(*)
    INTO v_total_investido, v_total_atual, v_total_rendimento, v_quantidade
    FROM investimentos 
    WHERE usuario_id = p_usuario_id;
    
    -- Calcular percentual
    IF v_total_investido > 0 THEN
        SET v_percentual = (v_total_rendimento / v_total_investido) * 100;
    END IF;
    
    -- Inserir ou atualizar snapshot
    INSERT INTO historico_carteira (
        usuario_id, data_snapshot, total_investido, total_atual, 
        total_rendimento, percentual_rendimento, quantidade_investimentos
    ) VALUES (
        p_usuario_id, v_hoje, v_total_investido, v_total_atual,
        v_total_rendimento, v_percentual, v_quantidade
    ) ON DUPLICATE KEY UPDATE
        total_investido = v_total_investido,
        total_atual = v_total_atual,
        total_rendimento = v_total_rendimento,
        percentual_rendimento = v_percentual,
        quantidade_investimentos = v_quantidade;
    
    -- Criar snapshots individuais dos investimentos
    INSERT INTO historico_investimentos (
        investimento_id, usuario_id, data_snapshot, valor_atual,
        rendimento, percentual_rendimento, preco_unitario
    )
    SELECT 
        id, usuario_id, v_hoje, valor_atual,
        rendimento, percentual_rendimento,
        CASE WHEN quantidade > 0 THEN valor_atual / quantidade ELSE 0 END
    FROM investimentos 
    WHERE usuario_id = p_usuario_id
    ON DUPLICATE KEY UPDATE
        valor_atual = VALUES(valor_atual),
        rendimento = VALUES(rendimento),
        percentual_rendimento = VALUES(percentual_rendimento),
        preco_unitario = VALUES(preco_unitario);
        
END //
DELIMITER ;

-- Comentários sobre as novas funcionalidades
/*
NOVAS FUNCIONALIDADES ADICIONADAS:

1. HISTÓRICO DA CARTEIRA:
   - Snapshots diários dos totais da carteira
   - Histórico detalhado por investimento
   - Possibilita gráficos de evolução temporal

2. METAS DE INVESTIMENTO:
   - Definir objetivos financeiros
   - Acompanhar progresso
   - Diferentes tipos de metas

3. ALERTAS PERSONALIZADOS:
   - Notificações por email/sistema
   - Alertas de preço e performance
   - Configuráveis por investimento

4. CONFIGURAÇÕES AVANÇADAS:
   - Preferências do usuário
   - Temas e notificações
   - Configurações de backup

5. CAMPOS EXTRAS:
   - Stop loss e take profit
   - Preços alvo
   - Última atualização de preços

COMO USAR:
1. Execute este script no seu banco de dados
2. As novas funcionalidades serão habilitadas automaticamente
3. Use a procedure CriarSnapshotCarteira() para histórico
4. Configure cron job para executar snapshots diários
*/ 