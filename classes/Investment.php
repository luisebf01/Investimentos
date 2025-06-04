<?php
/**
 * CLASSE DE INVESTIMENTOS
 * 
 * Esta classe gerencia tudo relacionado aos investimentos do usuário:
 * - Criar novos investimentos
 * - Editar investimentos existentes
 * - Buscar investimentos
 * - Excluir investimentos
 * - Calcular relatórios e estatísticas
 * - Gerenciar tipos de investimento
 */

// Incluir arquivo de conexão com banco de dados
require_once 'config/database.php';

class Investment {
    // Variável que guarda a conexão com o banco de dados
    private $conn;
    
    /**
     * CONSTRUTOR - Executado quando a classe é criada
     * Estabelece conexão com o banco de dados
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * BUSCAR TODOS OS INVESTIMENTOS DE UM USUÁRIO
     * 
     * @param int $user_id - ID do usuário que queremos buscar os investimentos
     * @return array - Lista de todos os investimentos do usuário
     */
    public function getAllByUser($user_id) {
        // Consulta SQL com JOIN para buscar investimentos + tipo
        // JOIN é como "juntar" duas tabelas para buscar dados relacionados
        $query = "SELECT i.*, t.nome as tipo_nome, t.cor as tipo_cor 
                  FROM investimentos i 
                  JOIN tipos_investimento t ON i.tipo_id = t.id 
                  WHERE i.usuario_id = :user_id 
                  ORDER BY i.valor_atual DESC";
        
        // Preparar e executar consulta
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Retornar todos os resultados como array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * BUSCAR UM INVESTIMENTO ESPECÍFICO
     * 
     * @param int $id - ID do investimento que queremos buscar
     * @param int $user_id - ID do usuário (para segurança - só pode ver seus próprios)
     * @return array|false - Dados do investimento ou false se não encontrar
     */
    public function getById($id, $user_id) {
        // Buscar investimento específico, mas só do usuário logado (segurança)
        $query = "SELECT i.*, t.nome as tipo_nome 
                  FROM investimentos i 
                  JOIN tipos_investimento t ON i.tipo_id = t.id 
                  WHERE i.id = :id AND i.usuario_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        // Retornar apenas um resultado
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * CRIAR NOVO INVESTIMENTO
     * 
     * @param array $data - Array com todos os dados do investimento
     * @return int|false - ID do novo investimento criado ou false se deu erro
     */
    public function create($data) {
        // Comando SQL para inserir novo investimento
        $query = "INSERT INTO investimentos (usuario_id, tipo_id, nome, ticker, quantidade, preco_medio, valor_investido, valor_atual, data_compra, observacoes) 
                  VALUES (:usuario_id, :tipo_id, :nome, :ticker, :quantidade, :preco_medio, :valor_investido, :valor_atual, :data_compra, :observacoes)";
        
        $stmt = $this->conn->prepare($query);
        
        // CALCULAR VALORES AUTOMATICAMENTE
        // Valor investido = quantidade × preço médio
        $valor_investido = $data['quantidade'] * $data['preco_medio'];
        
        // Se não informou valor atual, usar o valor investido
        $valor_atual = $data['valor_atual'] ?: $valor_investido;
        
        // Calcular rendimento (ganho ou perda)
        $rendimento = $valor_atual - $valor_investido;
        
        // Calcular percentual de rendimento
        $percentual_rendimento = $valor_investido > 0 ? ($rendimento / $valor_investido) * 100 : 0;
        
        // Preencher todos os parâmetros da consulta
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        $stmt->bindParam(':tipo_id', $data['tipo_id']);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':ticker', $data['ticker']);
        $stmt->bindParam(':quantidade', $data['quantidade']);
        $stmt->bindParam(':preco_medio', $data['preco_medio']);
        $stmt->bindParam(':valor_investido', $valor_investido);
        $stmt->bindParam(':valor_atual', $valor_atual);
        $stmt->bindParam(':data_compra', $data['data_compra']);
        $stmt->bindParam(':observacoes', $data['observacoes']);
        
        // Tentar executar o comando
        if($stmt->execute()) {
            // Se deu certo, pegar o ID do investimento criado
            $id = $this->conn->lastInsertId();
            
            // Atualizar os campos de rendimento
            $this->updateRendimento($id, $rendimento, $percentual_rendimento);
            
            return $id; // Retornar ID do novo investimento
        }
        
        return false; // Retornar false se deu erro
    }
    
    /**
     * ATUALIZAR INVESTIMENTO EXISTENTE
     * 
     * @param int $id - ID do investimento que será atualizado
     * @param array $data - Novos dados do investimento
     * @return bool - true se atualizou com sucesso, false se deu erro
     */
    public function update($id, $data) {
        // Comando SQL para atualizar investimento
        $query = "UPDATE investimentos SET 
                    tipo_id = :tipo_id, 
                    nome = :nome, 
                    ticker = :ticker, 
                    quantidade = :quantidade, 
                    preco_medio = :preco_medio, 
                    valor_atual = :valor_atual, 
                    data_compra = :data_compra, 
                    observacoes = :observacoes,
                    data_atualizacao = CURRENT_TIMESTAMP
                  WHERE id = :id AND usuario_id = :usuario_id";
        
        $stmt = $this->conn->prepare($query);
        
        // RECALCULAR VALORES com os novos dados
        $valor_investido = $data['quantidade'] * $data['preco_medio'];
        $valor_atual = $data['valor_atual'] ?: $valor_investido;
        $rendimento = $valor_atual - $valor_investido;
        $percentual_rendimento = $valor_investido > 0 ? ($rendimento / $valor_investido) * 100 : 0;
        
        // Preencher parâmetros
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':usuario_id', $data['usuario_id']);
        $stmt->bindParam(':tipo_id', $data['tipo_id']);
        $stmt->bindParam(':nome', $data['nome']);
        $stmt->bindParam(':ticker', $data['ticker']);
        $stmt->bindParam(':quantidade', $data['quantidade']);
        $stmt->bindParam(':preco_medio', $data['preco_medio']);
        $stmt->bindParam(':valor_atual', $valor_atual);
        $stmt->bindParam(':data_compra', $data['data_compra']);
        $stmt->bindParam(':observacoes', $data['observacoes']);
        
        if($stmt->execute()) {
            // Atualizar valores calculados em comando separado
            $update_query = "UPDATE investimentos SET 
                             valor_investido = :valor_investido,
                             rendimento = :rendimento,
                             percentual_rendimento = :percentual_rendimento
                             WHERE id = :id";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':id', $id);
            $update_stmt->bindParam(':valor_investido', $valor_investido);
            $update_stmt->bindParam(':rendimento', $rendimento);
            $update_stmt->bindParam(':percentual_rendimento', $percentual_rendimento);
            $update_stmt->execute();
            
            return true;
        }
        
        return false;
    }
    
    /**
     * EXCLUIR INVESTIMENTO
     * 
     * @param int $id - ID do investimento para excluir
     * @param int $user_id - ID do usuário (segurança - só pode excluir seus próprios)
     * @return bool - true se excluiu com sucesso, false se deu erro
     */
    public function delete($id, $user_id) {
        // Comando SQL para excluir (DELETE)
        // Só exclui se o investimento pertencer ao usuário (segurança)
        $query = "DELETE FROM investimentos WHERE id = :id AND usuario_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    /**
     * BUSCAR TODOS OS TIPOS DE INVESTIMENTO DISPONÍVEIS
     * (Ações, FIIs, Renda Fixa, etc.)
     * 
     * @return array - Lista de todos os tipos disponíveis
     */
    public function getTypes() {
        $query = "SELECT * FROM tipos_investimento ORDER BY nome";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * CALCULAR RESUMO GERAL DOS INVESTIMENTOS DO USUÁRIO
     * Retorna totais: valor investido, valor atual, rendimento, etc.
     * 
     * @param int $user_id - ID do usuário
     * @return array - Array com todos os totais calculados
     */
    public function getSummaryByUser($user_id) {
        // Consulta SQL usando funções de agregação (SUM, COUNT)
        // SUM = somar, COUNT = contar
        $query = "SELECT 
                    SUM(valor_investido) as total_investido,      -- Soma de todo dinheiro investido
                    SUM(valor_atual) as total_atual,             -- Soma do valor atual de tudo
                    SUM(rendimento) as total_rendimento,         -- Soma de todos os ganhos/perdas
                    COUNT(*) as total_investimentos              -- Quantidade total de investimentos
                  FROM investimentos 
                  WHERE usuario_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * CALCULAR RESUMO POR TIPO DE INVESTIMENTO
     * Mostra quanto o usuário tem em Ações, FIIs, Renda Fixa, etc.
     * 
     * @param int $user_id - ID do usuário
     * @return array - Lista com resumo de cada tipo de investimento
     */
    public function getSummaryByType($user_id) {
        // Consulta com GROUP BY para agrupar por tipo
        $query = "SELECT 
                    t.nome as tipo,                              -- Nome do tipo (Ações, FIIs, etc.)
                    t.cor as cor,                                -- Cor para exibir no gráfico
                    SUM(i.valor_investido) as valor_investido,  -- Total investido neste tipo
                    SUM(i.valor_atual) as valor_atual,          -- Valor atual deste tipo
                    SUM(i.rendimento) as rendimento,            -- Rendimento deste tipo
                    COUNT(i.id) as quantidade                   -- Quantos investimentos deste tipo
                  FROM investimentos i 
                  JOIN tipos_investimento t ON i.tipo_id = t.id 
                  WHERE i.usuario_id = :user_id 
                  GROUP BY t.id, t.nome, t.cor                  -- Agrupar por tipo
                  ORDER BY valor_atual DESC";                   // Ordenar do maior para o menor
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * MÉTODO PRIVADO - Atualizar campos de rendimento
     * Usado internamente pela classe para manter os cálculos atualizados
     * 
     * @param int $id - ID do investimento
     * @param float $rendimento - Valor do rendimento calculado
     * @param float $percentual_rendimento - Percentual do rendimento
     */
    private function updateRendimento($id, $rendimento, $percentual_rendimento) {
        $query = "UPDATE investimentos SET rendimento = :rendimento, percentual_rendimento = :percentual_rendimento WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':rendimento', $rendimento);
        $stmt->bindParam(':percentual_rendimento', $percentual_rendimento);
        $stmt->execute();
    }
}

/**
 * RESUMO DA CLASSE Investment:
 * 
 * MÉTODOS PRINCIPAIS:
 * 1. getAllByUser($user_id) - Busca todos investimentos do usuário
 * 2. getById($id, $user_id) - Busca um investimento específico
 * 3. create($data) - Cria novo investimento
 * 4. update($id, $data) - Atualiza investimento existente
 * 5. delete($id, $user_id) - Exclui investimento
 * 6. getTypes() - Lista tipos de investimento (Ações, FIIs, etc.)
 * 7. getSummaryByUser($user_id) - Relatório geral do usuário
 * 8. getSummaryByType($user_id) - Relatório por tipo de investimento
 * 
 * COMO USAR:
 * $investment = new Investment();
 * $lista = $investment->getAllByUser(1);
 * $resumo = $investment->getSummaryByUser(1);
 * $novo_id = $investment->create($dados);
 * 
 * CAMPOS DE UM INVESTIMENTO:
 * - nome: Nome do investimento (ex: "PETR4", "Tesouro IPCA")
 * - ticker: Código da ação (ex: "PETR4", "VALE3")
 * - tipo_id: Tipo do investimento (1=Ações, 2=FIIs, etc.)
 * - quantidade: Quantas unidades possui
 * - preco_medio: Preço médio pago por unidade
 * - valor_investido: Quanto dinheiro foi investido (calculado automaticamente)
 * - valor_atual: Valor atual do investimento
 * - rendimento: Ganho ou perda (calculado automaticamente)
 * - percentual_rendimento: Percentual de ganho/perda (calculado automaticamente)
 */
?> 