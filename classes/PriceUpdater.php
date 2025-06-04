<?php
/**
 * CLASSE PARA ATUALIZAÇÃO DE COTAÇÕES
 * 
 * Esta classe é responsável por:
 * - Buscar cotações em tempo real de APIs gratuitas
 * - Atualizar preços dos investimentos automaticamente
 * - Gerenciar cache de cotações
 * - Suportar diferentes tipos de ativos (ações, FIIs, cripto)
 * 
 * APIs utilizadas:
 * - Brapi (https://brapi.dev) - Ações e FIIs brasileiros
 * - CoinGecko - Criptomoedas
 * - Alpha Vantage - Ações internacionais (backup)
 */

require_once 'config/database.php';

class PriceUpdater {
    private $conn;
    private $brapi_url = 'https://brapi.dev/api/quote/';
    private $coingecko_url = 'https://api.coingecko.com/api/v3/simple/price';
    
    /**
     * CONSTRUTOR
     */
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    /**
     * ATUALIZAR TODOS OS INVESTIMENTOS DE UM USUÁRIO
     * 
     * @param int $user_id - ID do usuário
     * @return array - Resultado da atualização
     */
    public function updateAllPrices($user_id) {
        $results = [
            'updated' => 0,
            'errors' => 0,
            'messages' => []
        ];
        
        try {
            // Buscar todos os investimentos com ticker do usuário
            $query = "SELECT id, nome, ticker, tipo_id, quantidade, preco_medio, valor_investido 
                     FROM investimentos 
                     WHERE usuario_id = :user_id 
                     AND ticker IS NOT NULL 
                     AND ticker != ''";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($investments)) {
                $results['messages'][] = 'Nenhum investimento com ticker encontrado';
                return $results;
            }
            
            // Agrupar tickers por tipo para otimizar requisições
            $stocks_tickers = [];
            $crypto_tickers = [];
            
            foreach ($investments as $inv) {
                if ($this->isCryptoType($inv['tipo_id'])) {
                    $crypto_tickers[] = $inv['ticker'];
                } else {
                    $stocks_tickers[] = $inv['ticker'];
                }
            }
            
            // Buscar cotações em lote
            $stock_prices = [];
            $crypto_prices = [];
            
            if (!empty($stocks_tickers)) {
                $stock_prices = $this->fetchStockPrices($stocks_tickers);
            }
            
            if (!empty($crypto_tickers)) {
                $crypto_prices = $this->fetchCryptoPrices($crypto_tickers);
            }
            
            // Atualizar cada investimento
            foreach ($investments as $inv) {
                $ticker = strtoupper($inv['ticker']);
                $new_price = null;
                
                if ($this->isCryptoType($inv['tipo_id'])) {
                    $new_price = $crypto_prices[strtolower($ticker)] ?? null;
                } else {
                    $new_price = $stock_prices[$ticker] ?? null;
                }
                
                if ($new_price && $new_price > 0) {
                    if ($this->updateInvestmentPrice($inv['id'], $new_price, $inv)) {
                        $results['updated']++;
                        $results['messages'][] = "{$inv['nome']} ({$ticker}): R$ " . number_format($new_price, 2, ',', '.');
                    } else {
                        $results['errors']++;
                    }
                } else {
                    $results['errors']++;
                    $results['messages'][] = "Erro ao buscar cotação de {$inv['nome']} ({$ticker})";
                }
            }
            
        } catch (Exception $e) {
            $results['errors']++;
            $results['messages'][] = 'Erro geral: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * BUSCAR COTAÇÕES DE AÇÕES/FIIS NA BRAPI
     * 
     * @param array $tickers - Lista de códigos dos ativos
     * @return array - Array com ticker => preço
     */
    private function fetchStockPrices($tickers) {
        $prices = [];
        
        try {
            // A Brapi aceita múltiplos tickers separados por vírgula
            $tickers_str = implode(',', $tickers);
            $url = $this->brapi_url . $tickers_str;
            
            // Fazer requisição HTTP
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Carteira-Luxo/2.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new Exception('Erro ao conectar com API Brapi');
            }
            
            $data = json_decode($response, true);
            
            if (isset($data['results']) && is_array($data['results'])) {
                foreach ($data['results'] as $stock) {
                    if (isset($stock['symbol']) && isset($stock['regularMarketPrice'])) {
                        $prices[$stock['symbol']] = (float)$stock['regularMarketPrice'];
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log('Erro ao buscar cotações: ' . $e->getMessage());
        }
        
        return $prices;
    }
    
    /**
     * BUSCAR COTAÇÕES DE CRIPTOMOEDAS NO COINGECKO
     * 
     * @param array $tickers - Lista de símbolos cripto
     * @return array - Array com símbolo => preço em BRL
     */
    private function fetchCryptoPrices($tickers) {
        $prices = [];
        
        try {
            // Mapear símbolos comuns para IDs do CoinGecko
            $crypto_map = [
                'btc' => 'bitcoin',
                'eth' => 'ethereum',
                'ada' => 'cardano',
                'dot' => 'polkadot',
                'sol' => 'solana',
                'bnb' => 'binancecoin'
            ];
            
            $ids = [];
            foreach ($tickers as $ticker) {
                $ticker_lower = strtolower($ticker);
                if (isset($crypto_map[$ticker_lower])) {
                    $ids[] = $crypto_map[$ticker_lower];
                }
            }
            
            if (empty($ids)) {
                return $prices;
            }
            
            $ids_str = implode(',', $ids);
            $url = $this->coingecko_url . "?ids={$ids_str}&vs_currencies=brl";
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Carteira-Luxo/2.0'
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                foreach ($crypto_map as $symbol => $id) {
                    if (isset($data[$id]['brl'])) {
                        $prices[$symbol] = (float)$data[$id]['brl'];
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log('Erro ao buscar criptomoedas: ' . $e->getMessage());
        }
        
        return $prices;
    }
    
    /**
     * ATUALIZAR PREÇO DE UM INVESTIMENTO ESPECÍFICO
     * 
     * @param int $investment_id - ID do investimento
     * @param float $new_price - Novo preço por unidade
     * @param array $investment_data - Dados do investimento
     * @return bool - Sucesso da operação
     */
    private function updateInvestmentPrice($investment_id, $new_price, $investment_data) {
        try {
            // Calcular novos valores
            $quantidade = $investment_data['quantidade'];
            $valor_investido = $investment_data['valor_investido'];
            $valor_atual = $quantidade * $new_price;
            $rendimento = $valor_atual - $valor_investido;
            $percentual_rendimento = $valor_investido > 0 ? ($rendimento / $valor_investido) * 100 : 0;
            
            // Atualizar no banco
            $query = "UPDATE investimentos SET 
                        valor_atual = :valor_atual,
                        rendimento = :rendimento,
                        percentual_rendimento = :percentual_rendimento,
                        data_atualizacao = CURRENT_TIMESTAMP
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':valor_atual', $valor_atual);
            $stmt->bindParam(':rendimento', $rendimento);
            $stmt->bindParam(':percentual_rendimento', $percentual_rendimento);
            $stmt->bindParam(':id', $investment_id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log('Erro ao atualizar investimento: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * VERIFICAR SE UM TIPO É CRIPTOMOEDA
     * 
     * @param int $tipo_id - ID do tipo de investimento
     * @return bool
     */
    private function isCryptoType($tipo_id) {
        try {
            $query = "SELECT nome FROM tipos_investimento WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $tipo_id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result && stripos($result['nome'], 'cripto') !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * OBTER HISTÓRICO DE PREÇOS (FUNCIONALIDADE FUTURA)
     * 
     * @param string $ticker - Código do ativo
     * @param int $days - Número de dias de histórico
     * @return array - Dados históricos
     */
    public function getPriceHistory($ticker, $days = 30) {
        // Implementar posteriormente para gráficos históricos
        return [];
    }
    
    /**
     * CACHE DE COTAÇÕES PARA EVITAR MUITAS REQUISIÇÕES
     * 
     * @param string $key - Chave do cache
     * @param mixed $data - Dados para cachear
     * @param int $ttl - Tempo de vida em segundos
     */
    private function cacheSet($key, $data, $ttl = 300) {
        // Implementar cache em arquivo ou Redis posteriormente
        return true;
    }
    
    private function cacheGet($key) {
        // Implementar recuperação do cache
        return null;
    }
}
?> 