-- SISTEMA DE AUDITORIA COMPLETO
-- Execute este script para adicionar o sistema de histórico de transações/ações

USE carteira_investimentos;

-- Tabela principal de auditoria/logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    acao ENUM('create', 'update', 'delete', 'login', 'logout') NOT NULL,
    tabela_afetada VARCHAR(50) NOT NULL,
    registro_id INT,
    dados_anteriores JSON,
    dados_novos JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    detalhes TEXT,
    data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_data (usuario_id, data_acao),
    INDEX idx_tabela_registro (tabela_afetada, registro_id),
    INDEX idx_acao (acao)
);

-- Tabela para log de sessões de usuário
CREATE TABLE IF NOT EXISTS sessoes_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_ultimo_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    data_logout TIMESTAMP NULL,
    ativo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_ativo (usuario_id, ativo),
    INDEX idx_session_id (session_id)
);

-- Tabela para log específico de operações financeiras
CREATE TABLE IF NOT EXISTS audit_operacoes_financeiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_operacao ENUM('investimento_create', 'investimento_update', 'investimento_delete', 'meta_create', 'meta_update', 'meta_delete', 'transacao_create', 'transacao_update', 'transacao_delete') NOT NULL,
    registro_id INT NOT NULL,
    valor_anterior DECIMAL(15,2),
    valor_novo DECIMAL(15,2),
    quantidade_anterior DECIMAL(15,6),
    quantidade_nova DECIMAL(15,6),
    descricao TEXT,
    ip_address VARCHAR(45),
    data_operacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_operacao (usuario_id, tipo_operacao),
    INDEX idx_data_operacao (data_operacao)
);

-- View para facilitar consultas de auditoria
CREATE VIEW IF NOT EXISTS v_audit_resumo AS
SELECT 
    al.id,
    u.nome as usuario_nome,
    u.email as usuario_email,
    al.acao,
    al.tabela_afetada,
    al.registro_id,
    al.detalhes,
    al.ip_address,
    al.data_acao,
    CASE 
        WHEN al.tabela_afetada = 'investimentos' THEN i.nome
        WHEN al.tabela_afetada = 'metas_investimento' THEN m.titulo
        ELSE 'N/A'
    END as nome_registro
FROM audit_logs al
JOIN usuarios u ON al.usuario_id = u.id
LEFT JOIN investimentos i ON al.tabela_afetada = 'investimentos' AND al.registro_id = i.id
LEFT JOIN metas_investimento m ON al.tabela_afetada = 'metas_investimento' AND al.registro_id = m.id
ORDER BY al.data_acao DESC;

-- Procedure para limpar logs antigos (manter apenas últimos 6 meses)
DELIMITER //
CREATE PROCEDURE IF NOT EXISTS LimparLogsAntigos()
BEGIN
    DECLARE v_data_limite DATE DEFAULT DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
    
    -- Limpar audit_logs antigos
    DELETE FROM audit_logs WHERE data_acao < v_data_limite;
    
    -- Limpar sessões antigas (manter apenas últimos 3 meses)
    DELETE FROM sessoes_usuario WHERE data_login < DATE_SUB(CURDATE(), INTERVAL 3 MONTH);
    
    -- Limpar operações financeiras antigas (manter apenas últimos 12 meses)
    DELETE FROM audit_operacoes_financeiras WHERE data_operacao < DATE_SUB(CURDATE(), INTERVAL 12 MONTH);
    
    SELECT 'Logs antigos limpos com sucesso' as resultado;
END //
DELIMITER ;

-- Criar evento para limpeza automática mensal (se suportado)
SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS evt_limpeza_logs
ON SCHEDULE EVERY 1 MONTH
STARTS CURDATE()
DO
  CALL LimparLogsAntigos();

-- Inserir alguns logs de exemplo para teste
INSERT INTO audit_logs (usuario_id, acao, tabela_afetada, detalhes, ip_address) 
SELECT 
    id, 
    'create', 
    'sistema', 
    'Sistema de auditoria implementado', 
    '127.0.0.1'
FROM usuarios 
WHERE email = 'admin@carteira.com'; 