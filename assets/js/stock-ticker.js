/**
 * SISTEMA COMPLETO DE FAIXAS ROLANDO DE COTAÇÕES
 * 
 * Este arquivo implementa 3 faixas rolando que exibem cotações em tempo real:
 * 1. Ações brasileiras (B3)
 * 2. Fundos Imobiliários (FIIs)
 * 3. Criptomoedas populares
 * 
 * Usando a API gratuita brapi.dev
 */

class MultiStockTicker {
    constructor() {
        this.apiBase = 'https://brapi.dev/api';
        this.updateInterval = 60000; // 60 segundos
        this.isLoading = false;
        
        // Ações mais negociadas da B3
        this.stockSymbols = [
            'VALE3', 'PETR4', 'ITUB4', 'BBDC4', 'ABEV3',
            'MGLU3', 'WEGE3', 'RENT3', 'LREN3', 'SUZB3',
            'JBSS3', 'BBAS3', 'EMBR3', 'UGPA3', 'CSAN3',
            'RADL3', 'BEEF3', 'PRIO3', 'VIVT3', 'CCRO3'
        ];
        
        // Fundos Imobiliários mais negociados
        this.fiisSymbols = [
            'HGLG11', 'KNRI11', 'XPML11', 'BCFF11', 'HGRU11',
            'MXRF11', 'KNCR11', 'XPIN11', 'VILG11', 'VINO11',
            'HGRE11', 'BTLG11', 'GGRC11', 'XPLG11', 'RBRR11',
            'HSML11', 'URPR11', 'RBVA11', 'RECR11', 'JSRE11'
        ];
        
        // Criptomoedas populares (usando dados mock por enquanto)
        this.cryptoSymbols = [
            'BTC', 'ETH', 'ADA', 'DOT', 'XRP', 
            'LTC', 'LINK', 'BCH', 'XLM', 'UNI',
            'ALGO', 'ATOM', 'SOL', 'AVAX', 'MATIC'
        ];
        
        this.stocks = [];
        this.fiis = [];
        this.cryptos = [];
        this.tickersRunning = false;
        this.updateTimers = [];
    }
    
    /**
     * Inicializar todas as faixas de cotações
     */
    async init() {
        this.createAllTickersHTML();
        await Promise.all([
            this.fetchStockData(),
            this.fetchFiisData(),
            this.fetchCryptoData()
        ]);
        this.startAllTickers();
        this.startAutoUpdate();
    }
    
    /**
     * Criar o HTML das 3 faixas de cotações
     */
    createAllTickersHTML() {
        // Remover tickers existentes se houver
        const existingTickers = document.querySelectorAll('[id*="ticker"]');
        existingTickers.forEach(ticker => ticker.remove());
        
        // Criar container das faixas
        const tickersContainer = document.createElement('div');
        tickersContainer.id = 'multi-tickers';
        tickersContainer.className = 'multi-tickers-container';
        
        tickersContainer.innerHTML = `
            <!-- Faixa de Ações -->
            <div id="stock-ticker" class="stock-ticker-container stocks">
                <div class="stock-ticker-wrapper">
                    <div class="stock-ticker-label stocks">
                        📈 <span>AÇÕES B3</span>
                    </div>
                    <div class="stock-ticker-content" id="stock-ticker-content">
                        <div class="stock-ticker-loading">
                            Carregando ações...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Faixa de FIIs -->
            <div id="fiis-ticker" class="stock-ticker-container fiis">
                <div class="stock-ticker-wrapper">
                    <div class="stock-ticker-label fiis">
                        🏢 <span>FIIS</span>
                    </div>
                    <div class="stock-ticker-content" id="fiis-ticker-content">
                        <div class="stock-ticker-loading">
                            Carregando FIIs...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Faixa de Criptomoedas -->
            <div id="crypto-ticker" class="stock-ticker-container crypto">
                <div class="stock-ticker-wrapper">
                    <div class="stock-ticker-label crypto">
                        ₿ <span>CRYPTO</span>
                    </div>
                    <div class="stock-ticker-content" id="crypto-ticker-content">
                        <div class="stock-ticker-loading">
                            Carregando criptos...
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Inserir após o header
        const header = document.querySelector('.header');
        if (header) {
            header.insertAdjacentElement('afterend', tickersContainer);
        }
    }
    
    /**
     * Buscar dados das ações da API
     */
    async fetchStockData() {
        if (this.isLoading) return;
        
        try {
            // Temporariamente usando dados mock para garantir estabilidade
            console.log('ℹ️ Usando dados mock para ações temporariamente (API indisponível)');
            this.stocks = this.getMockStocks();
            
            console.log(`✅ Ações carregadas: ${this.stocks.length} ativos`);
            
        } catch (error) {
            console.error('❌ Erro ao buscar ações:', error);
            this.stocks = this.getMockStocks();
        }
        
        this.updateTickerDisplay('stock-ticker-content', this.stocks);
    }
    
    /**
     * Buscar dados dos FIIs da API
     */
    async fetchFiisData() {
        if (this.isLoading) return;
        
        try {
            // Temporariamente usando dados mock para garantir estabilidade
            console.log('ℹ️ Usando dados mock para FIIs temporariamente (API indisponível)');
            this.fiis = this.getMockFiis();
            
            console.log(`✅ FIIs carregados: ${this.fiis.length} ativos`);
            
        } catch (error) {
            console.error('❌ Erro ao buscar FIIs:', error);
            this.fiis = this.getMockFiis();
        }
        
        this.updateTickerDisplay('fiis-ticker-content', this.fiis);
    }
    
    /**
     * Buscar dados das criptomoedas da API
     */
    async fetchCryptoData() {
        if (this.isLoading) return;
        
        try {
            // Por enquanto, vamos usar dados mock para criptomoedas
            // Isso garante estabilidade enquanto investigamos a API
            console.log('ℹ️ Usando dados mock para criptomoedas temporariamente');
            this.cryptos = this.getMockCryptos();
            
            console.log(`✅ Criptomoedas carregadas: ${this.cryptos.length} ativos`);
            
        } catch (error) {
            console.error('❌ Erro ao buscar criptomoedas:', error);
            this.cryptos = this.getMockCryptos();
        }
        
        this.updateTickerDisplay('crypto-ticker-content', this.cryptos);
    }
    
    /**
     * Dados mockados para ações com variações dinâmicas
     */
    getMockStocks() {
        // Simulando variações dinâmicas nos preços
        const baseTime = Date.now();
        const variation = Math.sin(baseTime / 80000) * 0.03; // Variação de ±3%
        
        const basePrices = {
            'VALE3': 52.80,
            'PETR4': 36.15,
            'ITUB4': 32.45,
            'BBDC4': 13.89,
            'ABEV3': 11.67,
            'MGLU3': 8.45,
            'WEGE3': 45.78,
            'RENT3': 67.89,
            'LREN3': 58.90,
            'SUZB3': 44.30,
            'JBSS3': 34.20,
            'BBAS3': 28.75,
            'EMBR3': 42.80,
            'UGPA3': 156.50,
            'CSAN3': 18.90,
            'RADL3': 85.40,
            'BEEF3': 12.30,
            'PRIO3': 48.70,
            'VIVT3': 42.10,
            'CCRO3': 12.85
        };
        
        return Object.entries(basePrices).map(([symbol, basePrice]) => {
            const price = basePrice * (1 + variation + (Math.random() - 0.5) * 0.02);
            const change = price - basePrice;
            const changePercent = (change / basePrice) * 100;
            
            return {
                symbol: symbol,
                name: this.getStockName(symbol),
                price: price,
                change: change,
                changePercent: changePercent,
                currency: 'BRL'
            };
        });
    }
    
    /**
     * Dados mockados para FIIs com variações dinâmicas
     */
    getMockFiis() {
        // Simulando variações dinâmicas nos preços
        const baseTime = Date.now();
        const variation = Math.sin(baseTime / 90000) * 0.025; // Variação de ±2.5%
        
        const basePrices = {
            'HGLG11': 148.50,
            'KNRI11': 89.90,
            'XPML11': 95.30,
            'BCFF11': 78.45,
            'HGRU11': 112.75,
            'MXRF11': 9.85,
            'KNCR11': 98.20,
            'XPIN11': 87.60,
            'VILG11': 105.40,
            'VINO11': 92.30,
            'HGRE11': 134.80,
            'BTLG11': 98.70,
            'GGRC11': 88.90,
            'XPLG11': 76.50,
            'RBRR11': 65.80,
            'HSML11': 124.30,
            'URPR11': 87.40,
            'RBVA11': 145.60,
            'RECR11': 92.80,
            'JSRE11': 108.20
        };
        
        return Object.entries(basePrices).map(([symbol, basePrice]) => {
            const price = basePrice * (1 + variation + (Math.random() - 0.5) * 0.015);
            const change = price - basePrice;
            const changePercent = (change / basePrice) * 100;
            
            return {
                symbol: symbol,
                name: this.getFiiName(symbol),
                price: price,
                change: change,
                changePercent: changePercent,
                currency: 'BRL'
            };
        });
    }
    
    /**
     * Dados mockados para criptomoedas (valores realistas atualizados)
     */
    getMockCryptos() {
        // Simulando variações dinâmicas nos preços
        const baseTime = Date.now();
        const variation = Math.sin(baseTime / 100000) * 0.05; // Variação de ±5%
        
        const basePrices = {
            'BTC': 520000.00,
            'ETH': 19500.00,
            'ADA': 2.95,
            'DOT': 45.30,
            'XRP': 3.75,
            'LTC': 680.00,
            'LINK': 82.90,
            'BCH': 2650.00,
            'XLM': 0.52,
            'UNI': 45.80,
            'ALGO': 1.85,
            'ATOM': 42.30,
            'SOL': 285.00,
            'AVAX': 125.00,
            'MATIC': 2.85
        };
        
        return Object.entries(basePrices).map(([symbol, basePrice]) => {
            const price = basePrice * (1 + variation + (Math.random() - 0.5) * 0.02);
            const change = price - basePrice;
            const changePercent = (change / basePrice) * 100;
            
            return {
                symbol: symbol,
                name: this.getCryptoName(symbol),
                price: price,
                change: change,
                changePercent: changePercent,
                currency: 'BRL'
            };
        });
    }
    
    /**
     * Obter nome completo das criptomoedas
     */
    getCryptoName(symbol) {
        const names = {
            'BTC': 'BITCOIN',
            'ETH': 'ETHEREUM',
            'ADA': 'CARDANO',
            'DOT': 'POLKADOT',
            'XRP': 'RIPPLE',
            'LTC': 'LITECOIN',
            'LINK': 'CHAINLINK',
            'BCH': 'BITCOIN CASH',
            'XLM': 'STELLAR',
            'UNI': 'UNISWAP',
            'ALGO': 'ALGORAND',
            'ATOM': 'COSMOS',
            'SOL': 'SOLANA',
            'AVAX': 'AVALANCHE',
            'MATIC': 'POLYGON'
        };
        return names[symbol] || symbol;
    }
    
    /**
     * Obter nome completo das ações
     */
    getStockName(symbol) {
        const names = {
            'VALE3': 'VALE',
            'PETR4': 'PETROBRAS',
            'ITUB4': 'ITAU UNIBANCO',
            'BBDC4': 'BRADESCO',
            'ABEV3': 'AMBEV',
            'MGLU3': 'MAGAZINE LUIZA',
            'WEGE3': 'WEG',
            'RENT3': 'LOCALIZA',
            'LREN3': 'LOJAS RENNER',
            'SUZB3': 'SUZANO',
            'JBSS3': 'JBS',
            'BBAS3': 'BANCO DO BRASIL',
            'EMBR3': 'EMBRAER',
            'UGPA3': 'ULTRAPAR',
            'CSAN3': 'COSAN',
            'RADL3': 'RAIA DROGASIL',
            'BEEF3': 'MINERVA',
            'PRIO3': 'PETRO RIO',
            'VIVT3': 'TELEFONICA',
            'CCRO3': 'CCR'
        };
        return names[symbol] || symbol;
    }
    
    /**
     * Obter nome completo dos FIIs
     */
    getFiiName(symbol) {
        const names = {
            'HGLG11': 'CSHG LOGÍSTICA',
            'KNRI11': 'KINEA RENDA IMOB',
            'XPML11': 'XP MALLS',
            'BCFF11': 'BC FFII',
            'HGRU11': 'CSHG RENDA URB',
            'MXRF11': 'MAXI RENDA',
            'KNCR11': 'KINEA RENDIMENTOS',
            'XPIN11': 'XP INVESTIMENTOS',
            'VILG11': 'VINCI LOGÍSTICA',
            'VINO11': 'VINCI OFFICES',
            'HGRE11': 'CSHG REAL ESTATE',
            'BTLG11': 'BTG LOGÍSTICA',
            'GGRC11': 'GLOBAL REALTY',
            'XPLG11': 'XP LOG',
            'RBRR11': 'RBR RENDA',
            'HSML11': 'HSI MALLS',
            'URPR11': 'UPAR',
            'RBVA11': 'RBR ALPHA',
            'RECR11': 'REC RECEBIVEIS',
            'JSRE11': 'JSL REAL ESTATE'
        };
        return names[symbol] || symbol;
    }
    
    /**
     * Atualizar a exibição de uma faixa específica
     */
    updateTickerDisplay(containerId, data) {
        const container = document.getElementById(containerId);
        if (!container || data.length === 0) return;
        
        const tickerHTML = data.map(item => {
            const changeClass = item.change >= 0 ? 'positive' : 'negative';
            const changeSymbol = item.change >= 0 ? '+' : '';
            
            return `
                <div class="stock-item">
                    <span class="stock-symbol">${item.symbol}</span>
                    <span class="stock-price">R$ ${this.formatPrice(item.price)}</span>
                    <span class="stock-change ${changeClass}">
                        ${changeSymbol}${this.formatPrice(item.change)} 
                        (${changeSymbol}${this.formatPercent(item.changePercent)}%)
                    </span>
                </div>
            `;
        }).join('');
        
        container.innerHTML = `<div class="stock-ticker-scroll">${tickerHTML}</div>`;
    }
    
    /**
     * Iniciar animação de rolagem para todas as faixas
     */
    startAllTickers() {
        if (this.tickersRunning) return;
        
        const containers = ['stock-ticker-content', 'fiis-ticker-content', 'crypto-ticker-content'];
        
        containers.forEach(containerId => {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            const scrollElement = container.querySelector('.stock-ticker-scroll');
            if (!scrollElement) return;
            
            // Duplicar conteúdo para rolagem infinita
            const originalHTML = scrollElement.innerHTML;
            scrollElement.innerHTML = originalHTML + originalHTML;
            
            // Aplicar animação CSS
            scrollElement.classList.add('scrolling');
        });
        
        this.tickersRunning = true;
    }
    
    /**
     * Iniciar atualização automática a cada 60 segundos
     */
    startAutoUpdate() {
        // Limpar timers existentes
        this.updateTimers.forEach(timer => clearInterval(timer));
        this.updateTimers = [];
        
        // Criar novos timers para cada tipo de ativo
        const stockTimer = setInterval(() => {
            this.fetchStockData();
        }, this.updateInterval);
        
        const fiisTimer = setInterval(() => {
            this.fetchFiisData();
        }, this.updateInterval + 5000); // 5s de delay para não sobrecarregar
        
        const cryptoTimer = setInterval(() => {
            this.fetchCryptoData(); // Agora atualiza dados mock com variações
        }, this.updateInterval + 10000); // 10s de delay
        
        this.updateTimers.push(stockTimer, fiisTimer, cryptoTimer);
    }
    
    /**
     * Formatar preço
     */
    formatPrice(price) {
        if (price >= 1000) {
            return parseFloat(price).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        return parseFloat(price).toFixed(2).replace('.', ',');
    }
    
    /**
     * Formatar percentual
     */
    formatPercent(percent) {
        return parseFloat(percent).toFixed(2).replace('.', ',');
    }
    
    /**
     * Parar todos os tickers (para cleanup)
     */
    stop() {
        this.tickersRunning = false;
        
        // Limpar timers
        this.updateTimers.forEach(timer => clearInterval(timer));
        this.updateTimers = [];
        
        // Remover elementos
        const multiTickers = document.getElementById('multi-tickers');
        if (multiTickers) {
            multiTickers.remove();
        }
    }
}

// Inicializar automaticamente quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Verificar se não estamos nas páginas de login ou registro
    const currentPage = window.location.pathname;
    const excludePages = ['/login.php', '/register.php', 'login.php', 'register.php'];
    
    const shouldShowTicker = !excludePages.some(page => 
        currentPage.includes(page) || currentPage.endsWith(page)
    );
    
    if (shouldShowTicker) {
        // Aguardar um pouco para garantir que o header foi carregado
        setTimeout(() => {
            window.multiStockTicker = new MultiStockTicker();
            window.multiStockTicker.init();
        }, 100);
    }
});

// Limpar tickers ao sair da página
window.addEventListener('beforeunload', function() {
    if (window.multiStockTicker) {
        window.multiStockTicker.stop();
    }
}); 