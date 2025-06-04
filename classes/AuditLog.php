<?php
/**
 * CLASSE DE AUDITORIA E HISTÓRICO
 * 
 * Esta classe gerencia todo o sistema de logs e auditoria:
 * - Registrar ações dos usuários (criar, editar, excluir)
 * - Controlar sessões de usuário
 * - Log de operações financeiras
 * - Consultas de histórico
 * - Relatórios de auditoria
 */

require_once 'config/database.php';

class AuditLog {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * REGISTRAR AÇÃO GERAL NO SISTEMA
     * 
     * @param int $usuario_id - ID do usuário que executou a ação
     * @param string $acao - Tipo de ação (create, update, delete, login, logout)
     * @param string $tabela - Nome da tabela afetada
     * @param int $registro_id - ID do registro afetado (opcional)
     * @param array $dados_anteriores - Dados antes da alteração (opcional)
     * @param array $dados_novos - Dados após a alteração (opcional)  
     * @param string $detalhes - Descrição adicional da ação
     * @return bool - Sucesso ou falha
     */
    public function registrarAcao($usuario_id, $acao, $tabela, $registro_id = null, $dados_anteriores = null, $dados_novos = null, $detalhes = '') {
        $query = "INSERT INTO audit_logs (usuario_id, acao, tabela_afetada, registro_id, dados_anteriores, dados_novos, ip_address, user_agent, detalhes) 
                  VALUES (:usuario_id, :acao, :tabela, :registro_id, :dados_anteriores, :dados_novos, :ip_address, :user_agent, :detalhes)";
        
        $stmt = $this->conn->prepare($query);
        
        // Obter informações do cliente
        $ip_address = $this->getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
        
        // Converter arrays para JSON se necessário
        $dados_anteriores_json = $dados_anteriores ? json_encode($dados_anteriores, JSON_UNESCAPED_UNICODE) : null;
        $dados_novos_json = $dados_novos ? json_encode($dados_novos, JSON_UNESCAPED_UNICODE) : null;
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':acao', $acao);
        $stmt->bindParam(':tabela', $tabela);
        $stmt->bindParam(':registro_id', $registro_id);
        $stmt->bindParam(':dados_anteriores', $dados_anteriores_json);
        $stmt->bindParam(':dados_novos', $dados_novos_json);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->bindParam(':detalhes', $detalhes);
        
        return $stmt->execute();
    }
    
    /**
     * REGISTRAR OPERAÇÃO FINANCEIRA ESPECÍFICA
     * 
     * @param int $usuario_id - ID do usuário
     * @param string $tipo_operacao - Tipo específico da operação financeira
     * @param int $registro_id - ID do registro afetado
     * @param float $valor_anterior - Valor antes da alteração
     * @param float $valor_novo - Valor após a alteração
     * @param float $quantidade_anterior - Quantidade antes
     * @param float $quantidade_nova - Quantidade após
     * @param string $descricao - Descrição da operação
     * @return bool - Sucesso ou falha
     */
    public function registrarOperacaoFinanceira($usuario_id, $tipo_operacao, $registro_id, $valor_anterior = null, $valor_novo = null, $quantidade_anterior = null, $quantidade_nova = null, $descricao = '') {
        $query = "INSERT INTO audit_operacoes_financeiras (usuario_id, tipo_operacao, registro_id, valor_anterior, valor_novo, quantidade_anterior, quantidade_nova, descricao, ip_address) 
                  VALUES (:usuario_id, :tipo_operacao, :registro_id, :valor_anterior, :valor_novo, :quantidade_anterior, :quantidade_nova, :descricao, :ip_address)";
        
        $stmt = $this->conn->prepare($query);
        
        $ip_address = $this->getClientIP();
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':tipo_operacao', $tipo_operacao);
        $stmt->bindParam(':registro_id', $registro_id);
        $stmt->bindParam(':valor_anterior', $valor_anterior);
        $stmt->bindParam(':valor_novo', $valor_novo);
        $stmt->bindParam(':quantidade_anterior', $quantidade_anterior);
        $stmt->bindParam(':quantidade_nova', $quantidade_nova);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':ip_address', $ip_address);
        
        return $stmt->execute();
    }
    
    /**
     * REGISTRAR SESSÃO DE USUÁRIO (LOGIN)
     * 
     * @param int $usuario_id - ID do usuário
     * @param string $session_id - ID da sessão
     * @return int|false - ID da sessão criada ou false
     */
    public function registrarLogin($usuario_id, $session_id) {
        // Primeiro, marcar sessões antigas como inativas
        $this->finalizarSessoesAnteriores($usuario_id);
        
        $query = "INSERT INTO sessoes_usuario (usuario_id, session_id, ip_address, user_agent) 
                  VALUES (:usuario_id, :session_id, :ip_address, :user_agent)";
        
        $stmt = $this->conn->prepare($query);
        
        $ip_address = $this->getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
        
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':session_id', $session_id);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        
        if ($stmt->execute()) {
            // Registrar também no log geral
            $this->registrarAcao($usuario_id, 'login', 'sessoes_usuario', null, null, null, 'Login realizado');
            return $this->conn->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * REGISTRAR LOGOUT DE USUÁRIO
     * 
     * @param int $usuario_id - ID do usuário
     * @param string $session_id - ID da sessão
     * @return bool - Sucesso ou falha
     */
    public function registrarLogout($usuario_id, $session_id) {
        $query = "UPDATE sessoes_usuario SET data_logout = CURRENT_TIMESTAMP, ativo = FALSE 
                  WHERE usuario_id = :usuario_id AND session_id = :session_id AND ativo = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':session_id', $session_id);
        
        if ($stmt->execute()) {
            // Registrar também no log geral
            $this->registrarAcao($usuario_id, 'logout', 'sessoes_usuario', null, null, null, 'Logout realizado');
            return true;
        }
        
        return false;
    }
    
    /**
     * BUSCAR HISTÓRICO DE AÇÕES DO USUÁRIO
     * 
     * @param int $usuario_id - ID do usuário
     * @param int $limite - Número máximo de registros
     * @param int $offset - Número de registros para pular
     * @param string $filtro_acao - Filtrar por tipo de ação (opcional)
     * @param string $filtro_tabela - Filtrar por tabela (opcional)
     * @return array - Lista de logs
     */
    public function getHistoricoUsuario($usuario_id, $limite = 50, $offset = 0, $filtro_acao = '', $filtro_tabela = '') {
        $where_conditions = ["al.usuario_id = :usuario_id"];
        $params = [':usuario_id' => $usuario_id];
        
        if (!empty($filtro_acao)) {
            $where_conditions[] = "al.acao = :filtro_acao";
            $params[':filtro_acao'] = $filtro_acao;
        }
        
        if (!empty($filtro_tabela)) {
            $where_conditions[] = "al.tabela_afetada = :filtro_tabela";
            $params[':filtro_tabela'] = $filtro_tabela;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT 
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
                  WHERE {$where_clause} 
                  ORDER BY al.data_acao DESC LIMIT :limite OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * CONTAR TOTAL DE LOGS DO USUÁRIO
     */
    public function contarLogsUsuario($usuario_id, $filtro_acao = '', $filtro_tabela = '') {
        $where_conditions = ["usuario_id = :usuario_id"];
        $params = [':usuario_id' => $usuario_id];
        
        if (!empty($filtro_acao)) {
            $where_conditions[] = "acao = :filtro_acao";
            $params[':filtro_acao'] = $filtro_acao;
        }
        
        if (!empty($filtro_tabela)) {
            $where_conditions[] = "tabela_afetada = :filtro_tabela";
            $params[':filtro_tabela'] = $filtro_tabela;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT COUNT(*) as total FROM audit_logs WHERE {$where_clause}";
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] ?? 0;
    }
    
    /**
     * OBTER ESTATÍSTICAS DE ATIVIDADE
     */
    public function getEstatisticasAtividade($usuario_id, $dias = 30) {
        $query = "SELECT 
                    acao,
                    COUNT(*) as quantidade,
                    DATE(data_acao) as data
                  FROM audit_logs 
                  WHERE usuario_id = :usuario_id 
                    AND data_acao >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                  GROUP BY acao, DATE(data_acao)
                  ORDER BY DATE(data_acao) DESC, acao";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':dias', $dias);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * BUSCAR OPERAÇÕES FINANCEIRAS RECENTES
     */
    public function getOperacoesFinanceiras($usuario_id, $limite = 20) {
        $query = "SELECT 
                    oaf.*,
                    CASE 
                        WHEN oaf.tipo_operacao LIKE 'investimento%' THEN i.nome
                        WHEN oaf.tipo_operacao LIKE 'meta%' THEN m.titulo
                        ELSE 'Registro não encontrado'
                    END as nome_registro
                  FROM audit_operacoes_financeiras oaf
                  LEFT JOIN investimentos i ON oaf.tipo_operacao LIKE 'investimento%' AND oaf.registro_id = i.id
                  LEFT JOIN metas_investimento m ON oaf.tipo_operacao LIKE 'meta%' AND oaf.registro_id = m.id
                  WHERE oaf.usuario_id = :usuario_id
                  ORDER BY oaf.data_operacao DESC
                  LIMIT :limite";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * MÉTODOS AUXILIARES
     */
    
    private function getClientIP() {
        $ip_keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Se houver múltiplos IPs, pegar o primeiro
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validar IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    private function finalizarSessoesAnteriores($usuario_id) {
        $query = "UPDATE sessoes_usuario SET ativo = FALSE, data_logout = CURRENT_TIMESTAMP 
                  WHERE usuario_id = :usuario_id AND ativo = TRUE";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
    }
    
    /**
     * MÉTODO CONVENIENTE PARA REGISTRAR ALTERAÇÕES EM INVESTIMENTOS
     */
    public function logInvestimento($usuario_id, $acao, $investimento_id, $dados_anteriores = null, $dados_novos = null) {
        $acoes_map = [
            'create' => 'investimento_create',
            'update' => 'investimento_update', 
            'delete' => 'investimento_delete'
        ];
        
        // Log geral
        $detalhes = match($acao) {
            'create' => 'Novo investimento criado: ' . ($dados_novos['nome'] ?? 'Nome não informado'),
            'update' => 'Investimento atualizado: ' . ($dados_novos['nome'] ?? $dados_anteriores['nome'] ?? 'Nome não informado'),
            'delete' => 'Investimento excluído: ' . ($dados_anteriores['nome'] ?? 'Nome não informado'),
            default => 'Operação em investimento'
        };
        
        $this->registrarAcao($usuario_id, $acao, 'investimentos', $investimento_id, $dados_anteriores, $dados_novos, $detalhes);
        
        // Log financeiro específico
        if (isset($acoes_map[$acao])) {
            $valor_anterior = $dados_anteriores['valor_atual'] ?? null;
            $valor_novo = $dados_novos['valor_atual'] ?? null;
            $quantidade_anterior = $dados_anteriores['quantidade'] ?? null;
            $quantidade_nova = $dados_novos['quantidade'] ?? null;
            
            $this->registrarOperacaoFinanceira(
                $usuario_id, 
                $acoes_map[$acao], 
                $investimento_id, 
                $valor_anterior, 
                $valor_novo, 
                $quantidade_anterior, 
                $quantidade_nova, 
                $detalhes
            );
        }
    }
    
    /**
     * MÉTODO CONVENIENTE PARA REGISTRAR ALTERAÇÕES EM METAS
     */
    public function logMeta($usuario_id, $acao, $meta_id, $dados_anteriores = null, $dados_novos = null) {
        $acoes_map = [
            'create' => 'meta_create',
            'update' => 'meta_update',
            'delete' => 'meta_delete'
        ];
        
        // Log geral
        $detalhes = match($acao) {
            'create' => 'Nova meta criada: ' . ($dados_novos['titulo'] ?? 'Título não informado'),
            'update' => 'Meta atualizada: ' . ($dados_novos['titulo'] ?? $dados_anteriores['titulo'] ?? 'Título não informado'),
            'delete' => 'Meta excluída: ' . ($dados_anteriores['titulo'] ?? 'Título não informado'),
            default => 'Operação em meta'
        };
        
        $this->registrarAcao($usuario_id, $acao, 'metas_investimento', $meta_id, $dados_anteriores, $dados_novos, $detalhes);
        
        // Log financeiro específico
        if (isset($acoes_map[$acao])) {
            $valor_anterior = $dados_anteriores['valor_meta'] ?? null;
            $valor_novo = $dados_novos['valor_meta'] ?? null;
            
            $this->registrarOperacaoFinanceira(
                $usuario_id, 
                $acoes_map[$acao], 
                $meta_id, 
                $valor_anterior, 
                $valor_novo, 
                null, 
                null, 
                $detalhes
            );
        }
    }
} 