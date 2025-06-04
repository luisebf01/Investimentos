<?php
/**
 * CALCULADORA DE INVESTIMENTOS
 * 
 * Funcionalidades:
 * - Juros simples e compostos
 * - Dividend yield
 * - Valor futuro com aportes
 * - Tempo para atingir meta
 * - Taxa de retorno necessária
 * - Preço alvo de ações
 * - Rentabilidade anualizada
 */

require_once 'classes/Auth.php';

$auth = new Auth();

// Verificar autenticação
$auth->requireLogin();
$user = $auth->getCurrentUser();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Investimentos - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .calculator-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .calculator-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
            border: 1px solid #444;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .calculator-card:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.1);
        }
        
        .calc-header {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .calc-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .calc-body {
            padding: 1.5rem;
        }
        
        .calc-input-group {
            margin-bottom: 1rem;
        }
        
        .calc-input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ccc;
            font-weight: 500;
        }
        
        .calc-input {
            width: 100%;
            padding: 0.75rem;
            background: #333;
            border: 1px solid #555;
            border-radius: 6px;
            color: #fff;
            font-size: 1rem;
            box-sizing: border-box;
        }
        
        .calc-input:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }
        
        .calc-button {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        
        .calc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
        }
        
        .calc-result {
            background: rgba(33, 150, 243, 0.1);
            border: 1px solid #2196F3;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
            text-align: center;
        }
        
        .calc-result-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2196F3;
            margin-bottom: 0.5rem;
        }
        
        .calc-result-label {
            color: #ccc;
            font-size: 0.9rem;
        }
        
        .calc-tabs {
            display: flex;
            background: #333;
            border-radius: 8px;
            padding: 0.25rem;
            margin-bottom: 2rem;
        }
        
        .calc-tab {
            flex: 1;
            padding: 0.75rem;
            background: transparent;
            border: none;
            color: #ccc;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .calc-tab.active {
            background: #2196F3;
            color: white;
        }
        
        .calc-section {
            display: none;
        }
        
        .calc-section.active {
            display: block;
        }
        
        .input-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .result-breakdown {
            background: #2a2a2a;
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .result-breakdown h4 {
            margin: 0 0 1rem 0;
            color: #2196F3;
        }
        
        .breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #444;
        }
        
        .breakdown-item:last-child {
            border-bottom: none;
        }
        
        .breakdown-label {
            color: #ccc;
        }
        
        .breakdown-value {
            color: #fff;
            font-weight: 600;
        }
        
        .calc-button-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .calc-button-secondary {
            flex: 1;
            padding: 0.75rem;
            background: linear-gradient(135deg, #666 0%, #555 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .calc-button-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(85, 85, 85, 0.3);
        }
        
        .calc-button {
            flex: 3;
        }
        
        .calculator-card {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 500px;
        }
        
        .calc-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .calc-content {
            flex: 1;
        }
        
        .period-selector {
            display: flex;
            background: #333;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 1rem;
        }
        
        .period-option {
            flex: 1;
            padding: 0.5rem;
            background: transparent;
            border: none;
            color: #ccc;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .period-option.active {
            background: #2196F3;
            color: white;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="metas.php">Metas</a></li>
                <li><a href="calculadora.php" class="active">Calculadora</a></li>
                <li><a href="relatorios.php">Relatórios</a></li>
                <li><a href="historico.php">Histórico</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>🧮 Calculadora de Investimentos</h1>
                    <p style="color: #ccc;">Ferramentas para planejamento e análise financeira</p>
                </div>
            </div>

            <!-- Tabs de Navegação -->
            <div class="calc-tabs">
                <button class="calc-tab active" onclick="showSection('juros')">Juros</button>
                <button class="calc-tab" onclick="showSection('aportes')">Aportes</button>
                <button class="calc-tab" onclick="showSection('dividendos')">Dividendos</button>
                <button class="calc-tab" onclick="showSection('metas')">Metas</button>
                <button class="calc-tab" onclick="showSection('acoes')">Ações</button>
            </div>

            <!-- SEÇÃO: JUROS SIMPLES E COMPOSTOS -->
            <div id="juros" class="calc-section active">
                <div class="calculator-grid">
                    <!-- Juros Simples -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>📈 Juros Simples</h3>
                        </div>
                        <div class="calc-body">
                            <div class="calc-content">
                                <div class="calc-input-group">
                                    <label>Capital Inicial (R$)</label>
                                    <input type="number" id="js_capital" class="calc-input" placeholder="10000" step="0.01">
                                </div>
                                <div class="input-row">
                                    <div class="calc-input-group">
                                        <label>Taxa (%)</label>
                                        <input type="number" id="js_taxa" class="calc-input" placeholder="12" step="0.01">
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Período</label>
                                        <input type="number" id="js_periodo" class="calc-input" placeholder="5" step="0.01">
                                    </div>
                                </div>
                                <div class="period-selector">
                                    <button class="period-option active" onclick="setPeriod('js', 'anos')">Anos</button>
                                    <button class="period-option" onclick="setPeriod('js', 'meses')">Meses</button>
                                    <button class="period-option" onclick="setPeriod('js', 'dias')">Dias</button>
                                </div>
                                <div id="js_resultado" class="calc-result" style="display: none;">
                                    <div class="calc-result-value" id="js_valor"></div>
                                    <div class="calc-result-label">Montante Final</div>
                                    <div class="result-breakdown" id="js_breakdown"></div>
                                </div>
                            </div>
                            <div class="calc-button-group">
                                <button class="calc-button-secondary" onclick="limparCalculo('js')">🗑️ Limpar</button>
                                <button class="calc-button" onclick="calcularJurosSimples()">Calcular Juros Simples</button>
                            </div>
                        </div>
                    </div>

                    <!-- Juros Compostos -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>🚀 Juros Compostos</h3>
                        </div>
                        <div class="calc-body">
                            <div class="calc-content">
                                <div class="calc-input-group">
                                    <label>Capital Inicial (R$)</label>
                                    <input type="number" id="jc_capital" class="calc-input" placeholder="10000" step="0.01">
                                </div>
                                <div class="input-row">
                                    <div class="calc-input-group">
                                        <label>Taxa (%)</label>
                                        <input type="number" id="jc_taxa" class="calc-input" placeholder="12" step="0.01">
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Período</label>
                                        <input type="number" id="jc_periodo" class="calc-input" placeholder="5" step="0.01">
                                    </div>
                                </div>
                                <div class="period-selector">
                                    <button class="period-option active" onclick="setPeriod('jc', 'anos')">Anos</button>
                                    <button class="period-option" onclick="setPeriod('jc', 'meses')">Meses</button>
                                    <button class="period-option" onclick="setPeriod('jc', 'dias')">Dias</button>
                                </div>
                                <div id="jc_resultado" class="calc-result" style="display: none;">
                                    <div class="calc-result-value" id="jc_valor"></div>
                                    <div class="calc-result-label">Montante Final</div>
                                    <div class="result-breakdown" id="jc_breakdown"></div>
                                </div>
                            </div>
                            <div class="calc-button-group">
                                <button class="calc-button-secondary" onclick="limparCalculo('jc')">🗑️ Limpar</button>
                                <button class="calc-button" onclick="calcularJurosCompostos()">Calcular Juros Compostos</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO: APORTES MENSAIS -->
            <div id="aportes" class="calc-section">
                <div class="calculator-grid">
                    <!-- Valor Futuro com Aportes -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>💰 Valor Futuro com Aportes</h3>
                        </div>
                        <div class="calc-body">
                            <div class="calc-content">
                                <div class="input-row">
                                    <div class="calc-input-group">
                                        <label>Capital Inicial (R$)</label>
                                        <input type="number" id="ap_capital" class="calc-input" placeholder="1000" step="0.01">
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Aporte Mensal (R$)</label>
                                        <input type="number" id="ap_aporte" class="calc-input" placeholder="500" step="0.01">
                                    </div>
                                </div>
                                <div class="input-row">
                                    <div class="calc-input-group">
                                        <label>Taxa (% ao mês)</label>
                                        <input type="number" id="ap_taxa" class="calc-input" placeholder="1" step="0.01">
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Período (meses)</label>
                                        <input type="number" id="ap_periodo" class="calc-input" placeholder="120" step="1">
                                    </div>
                                </div>
                                <div id="ap_resultado" class="calc-result" style="display: none;">
                                    <div class="calc-result-value" id="ap_valor"></div>
                                    <div class="calc-result-label">Valor Final</div>
                                    <div class="result-breakdown" id="ap_breakdown"></div>
                                </div>
                            </div>
                            <div class="calc-button-group">
                                <button class="calc-button-secondary" onclick="limparCalculo('ap')">🗑️ Limpar</button>
                                <button class="calc-button" onclick="calcularAportes()">Calcular Valor Futuro</button>
                            </div>
                        </div>
                    </div>

                    <!-- Aporte Necessário -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>🎯 Aporte Necessário para Meta</h3>
                        </div>
                        <div class="calc-body">
                            <div class="calc-content">
                                <div class="input-row">
                                    <div class="calc-input-group">
                                        <label>Capital Inicial (R$)</label>
                                        <input type="number" id="an_capital" class="calc-input" placeholder="5000" step="0.01">
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Valor Desejado (R$)</label>
                                        <input type="number" id="an_meta" class="calc-input" placeholder="100000" step="0.01">
                                    </div>
                                </div>
                                <div class="input-row">
                                    <div class="calc-input-group">
                                        <label>Taxa (% ao mês)</label>
                                        <input type="number" id="an_taxa" class="calc-input" placeholder="1" step="0.01">
                                    </div>
                                    <div class="calc-input-group">
                                        <label>Período (meses)</label>
                                        <input type="number" id="an_periodo" class="calc-input" placeholder="120" step="1">
                                    </div>
                                </div>
                                <div id="an_resultado" class="calc-result" style="display: none;">
                                    <div class="calc-result-value" id="an_valor"></div>
                                    <div class="calc-result-label">Aporte Mensal Necessário</div>
                                </div>
                            </div>
                            <div class="calc-button-group">
                                <button class="calc-button-secondary" onclick="limparCalculo('an')">🗑️ Limpar</button>
                                <button class="calc-button" onclick="calcularAporteNecessario()">Calcular Aporte</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO: DIVIDENDOS -->
            <div id="dividendos" class="calc-section">
                <div class="calculator-grid">
                    <!-- Dividend Yield -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>💵 Dividend Yield</h3>
                        </div>
                        <div class="calc-body">
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Preço da Ação (R$)</label>
                                    <input type="number" id="dy_preco" class="calc-input" placeholder="25.50" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>Dividendo Anual (R$)</label>
                                    <input type="number" id="dy_dividendo" class="calc-input" placeholder="1.20" step="0.01">
                                </div>
                            </div>
                            <button class="calc-button" onclick="calcularDividendYield()">Calcular Dividend Yield</button>
                            <div id="dy_resultado" class="calc-result" style="display: none;">
                                <div class="calc-result-value" id="dy_valor"></div>
                                <div class="calc-result-label">Dividend Yield Anual</div>
                            </div>
                        </div>
                    </div>

                    <!-- Renda Passiva -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>🏠 Renda Passiva de Dividendos</h3>
                        </div>
                        <div class="calc-body">
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Investimento Total (R$)</label>
                                    <input type="number" id="rp_investimento" class="calc-input" placeholder="100000" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>Dividend Yield (%)</label>
                                    <input type="number" id="rp_yield" class="calc-input" placeholder="6" step="0.01">
                                </div>
                            </div>
                            <button class="calc-button" onclick="calcularRendaPassiva()">Calcular Renda Passiva</button>
                            <div id="rp_resultado" class="calc-result" style="display: none;">
                                <div class="calc-result-value" id="rp_valor"></div>
                                <div class="calc-result-label">Renda Mensal Estimada</div>
                                <div class="result-breakdown" id="rp_breakdown"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO: METAS -->
            <div id="metas" class="calc-section">
                <div class="calculator-grid">
                    <!-- Tempo para Atingir Meta -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>⏰ Tempo para Atingir Meta</h3>
                        </div>
                        <div class="calc-body">
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Capital Inicial (R$)</label>
                                    <input type="number" id="tm_capital" class="calc-input" placeholder="5000" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>Valor da Meta (R$)</label>
                                    <input type="number" id="tm_meta" class="calc-input" placeholder="50000" step="0.01">
                                </div>
                            </div>
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Aporte Mensal (R$)</label>
                                    <input type="number" id="tm_aporte" class="calc-input" placeholder="800" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>Taxa (% ao mês)</label>
                                    <input type="number" id="tm_taxa" class="calc-input" placeholder="1" step="0.01">
                                </div>
                            </div>
                            <button class="calc-button" onclick="calcularTempoMeta()">Calcular Tempo</button>
                            <div id="tm_resultado" class="calc-result" style="display: none;">
                                <div class="calc-result-value" id="tm_valor"></div>
                                <div class="calc-result-label">Tempo Necessário</div>
                            </div>
                        </div>
                    </div>

                    <!-- Taxa Necessária -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>📊 Taxa Necessária para Meta</h3>
                        </div>
                        <div class="calc-body">
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Capital Inicial (R$)</label>
                                    <input type="number" id="tn_capital" class="calc-input" placeholder="10000" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>Valor da Meta (R$)</label>
                                    <input type="number" id="tn_meta" class="calc-input" placeholder="50000" step="0.01">
                                </div>
                            </div>
                            <div class="calc-input-group">
                                <label>Período (anos)</label>
                                <input type="number" id="tn_periodo" class="calc-input" placeholder="5" step="0.01">
                            </div>
                            <button class="calc-button" onclick="calcularTaxaNecessaria()">Calcular Taxa</button>
                            <div id="tn_resultado" class="calc-result" style="display: none;">
                                <div class="calc-result-value" id="tn_valor"></div>
                                <div class="calc-result-label">Taxa Anual Necessária</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEÇÃO: AÇÕES -->
            <div id="acoes" class="calc-section">
                <div class="calculator-grid">
                    <!-- Preço Alvo -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>🎯 Preço Alvo por P/L</h3>
                        </div>
                        <div class="calc-body">
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Lucro por Ação (R$)</label>
                                    <input type="number" id="pa_lpa" class="calc-input" placeholder="2.50" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>P/L Desejado</label>
                                    <input type="number" id="pa_pl" class="calc-input" placeholder="15" step="0.01">
                                </div>
                            </div>
                            <button class="calc-button" onclick="calcularPrecoAlvo()">Calcular Preço Alvo</button>
                            <div id="pa_resultado" class="calc-result" style="display: none;">
                                <div class="calc-result-value" id="pa_valor"></div>
                                <div class="calc-result-label">Preço Alvo da Ação</div>
                            </div>
                        </div>
                    </div>

                    <!-- Rentabilidade Anualizada -->
                    <div class="calculator-card">
                        <div class="calc-header">
                            <h3>📈 Rentabilidade Anualizada</h3>
                        </div>
                        <div class="calc-body">
                            <div class="input-row">
                                <div class="calc-input-group">
                                    <label>Valor Inicial (R$)</label>
                                    <input type="number" id="ra_inicial" class="calc-input" placeholder="10000" step="0.01">
                                </div>
                                <div class="calc-input-group">
                                    <label>Valor Final (R$)</label>
                                    <input type="number" id="ra_final" class="calc-input" placeholder="15000" step="0.01">
                                </div>
                            </div>
                            <div class="calc-input-group">
                                <label>Período (dias)</label>
                                <input type="number" id="ra_periodo" class="calc-input" placeholder="365" step="1">
                            </div>
                            <button class="calc-button" onclick="calcularRentabilidadeAnualizada()">Calcular Rentabilidade</button>
                            <div id="ra_resultado" class="calc-result" style="display: none;">
                                <div class="calc-result-value" id="ra_valor"></div>
                                <div class="calc-result-label">Rentabilidade Anualizada</div>
                                <div class="result-breakdown" id="ra_breakdown"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Navegação entre seções
        function showSection(sectionId) {
            // Esconder todas as seções
            document.querySelectorAll('.calc-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Remover classe active de todas as tabs
            document.querySelectorAll('.calc-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Mostrar seção selecionada
            document.getElementById(sectionId).classList.add('active');
            
            // Ativar tab correspondente
            event.target.classList.add('active');
        }

        // Formatação de números
        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        }

        function formatPercent(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'percent',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value / 100);
        }

        // Controle de períodos
        const periodSettings = {
            js: 'anos',
            jc: 'anos'
        };

        function setPeriod(calc, period) {
            periodSettings[calc] = period;
            
            // Atualizar UI
            const card = document.querySelector(`#juros .calculator-card:nth-child(${calc === 'js' ? '1' : '2'})`);
            const buttons = card.querySelectorAll('.period-option');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Atualizar placeholder do período
            const periodInput = document.getElementById(`${calc}_periodo`);
            switch(period) {
                case 'dias':
                    periodInput.placeholder = '365';
                    break;
                case 'meses':
                    periodInput.placeholder = '60';
                    break;
                case 'anos':
                    periodInput.placeholder = '5';
                    break;
            }
        }

        // Função para limpar cálculos
        function limparCalculo(prefix) {
            // Limpar todos os inputs possíveis
            const possibleInputs = [
                `${prefix}_capital`, `${prefix}_taxa`, `${prefix}_periodo`, 
                `${prefix}_aporte`, `${prefix}_meta`, `${prefix}_investimento`, 
                `${prefix}_yield`, `${prefix}_preco`, `${prefix}_dividendo`, 
                `${prefix}_lpa`, `${prefix}_pl`, `${prefix}_inicial`, `${prefix}_final`
            ];
            
            possibleInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input) input.value = '';
            });
            
            // Ocultar resultado
            const resultado = document.getElementById(`${prefix}_resultado`);
            if (resultado) {
                resultado.style.display = 'none';
            }
            
            // Resetar período para anos se aplicável
            if (prefix === 'js' || prefix === 'jc') {
                setPeriodDefault(prefix);
            }
        }

        function setPeriodDefault(calc) {
            periodSettings[calc] = 'anos';
            const card = document.querySelector(`#juros .calculator-card:nth-child(${calc === 'js' ? '1' : '2'})`);
            const buttons = card.querySelectorAll('.period-option');
            buttons.forEach(btn => btn.classList.remove('active'));
            buttons[0].classList.add('active'); // Ativar "Anos"
            
            const periodInput = document.getElementById(`${calc}_periodo`);
            periodInput.placeholder = '5';
        }

        // CÁLCULOS DE JUROS

        function calcularJurosSimples() {
            const capital = parseFloat(document.getElementById('js_capital').value) || 0;
            const taxa = parseFloat(document.getElementById('js_taxa').value) || 0;
            const periodo = parseFloat(document.getElementById('js_periodo').value) || 0;
            
            if (capital <= 0 || taxa <= 0 || periodo <= 0) {
                alert('Por favor, preencha todos os campos com valores positivos');
                return;
            }
            
            // Converter período para anos se necessário
            let periodoAnos = periodo;
            const tipoPeriodo = periodSettings.js;
            
            if (tipoPeriodo === 'meses') {
                periodoAnos = periodo / 12;
            } else if (tipoPeriodo === 'dias') {
                periodoAnos = periodo / 365;
            }
            
            const juros = capital * (taxa / 100) * periodoAnos;
            const montante = capital + juros;
            
            document.getElementById('js_valor').textContent = formatCurrency(montante);
            
            document.getElementById('js_breakdown').innerHTML = `
                <h4>Detalhamento</h4>
                <div class="breakdown-item">
                    <span class="breakdown-label">Capital Inicial:</span>
                    <span class="breakdown-value">${formatCurrency(capital)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Período:</span>
                    <span class="breakdown-value">${periodo} ${tipoPeriodo}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Juros Ganhos:</span>
                    <span class="breakdown-value">${formatCurrency(juros)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Rentabilidade Total:</span>
                    <span class="breakdown-value">${formatPercent((montante - capital) / capital * 100)}</span>
                </div>
            `;
            
            document.getElementById('js_resultado').style.display = 'block';
        }

        function calcularJurosCompostos() {
            const capital = parseFloat(document.getElementById('jc_capital').value) || 0;
            const taxa = parseFloat(document.getElementById('jc_taxa').value) || 0;
            const periodo = parseFloat(document.getElementById('jc_periodo').value) || 0;
            
            if (capital <= 0 || taxa <= 0 || periodo <= 0) {
                alert('Por favor, preencha todos os campos com valores positivos');
                return;
            }
            
            // Converter período para anos se necessário
            let periodoAnos = periodo;
            const tipoPeriodo = periodSettings.jc;
            
            if (tipoPeriodo === 'meses') {
                periodoAnos = periodo / 12;
            } else if (tipoPeriodo === 'dias') {
                periodoAnos = periodo / 365;
            }
            
            const montante = capital * Math.pow(1 + (taxa / 100), periodoAnos);
            const juros = montante - capital;
            
            document.getElementById('jc_valor').textContent = formatCurrency(montante);
            
            document.getElementById('jc_breakdown').innerHTML = `
                <h4>Detalhamento</h4>
                <div class="breakdown-item">
                    <span class="breakdown-label">Capital Inicial:</span>
                    <span class="breakdown-value">${formatCurrency(capital)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Período:</span>
                    <span class="breakdown-value">${periodo} ${tipoPeriodo}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Juros Compostos:</span>
                    <span class="breakdown-value">${formatCurrency(juros)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Rentabilidade Total:</span>
                    <span class="breakdown-value">${formatPercent((montante - capital) / capital * 100)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Taxa Equivalente Anual:</span>
                    <span class="breakdown-value">${formatPercent(Math.pow(montante / capital, 1 / periodoAnos) - 1)}</span>
                </div>
            `;
            
            document.getElementById('jc_resultado').style.display = 'block';
        }

        // CÁLCULOS DE APORTES

        function calcularAportes() {
            const capital = parseFloat(document.getElementById('ap_capital').value) || 0;
            const aporte = parseFloat(document.getElementById('ap_aporte').value) || 0;
            const taxa = parseFloat(document.getElementById('ap_taxa').value) || 0;
            const periodo = parseInt(document.getElementById('ap_periodo').value) || 0;
            
            if (taxa <= 0 || periodo <= 0) {
                alert('Por favor, preencha todos os campos com valores positivos');
                return;
            }
            
            const taxaDecimal = taxa / 100;
            
            // Valor futuro do capital inicial
            const vfCapital = capital * Math.pow(1 + taxaDecimal, periodo);
            
            // Valor futuro dos aportes (série uniforme)
            const vfAportes = aporte > 0 ? aporte * (Math.pow(1 + taxaDecimal, periodo) - 1) / taxaDecimal : 0;
            
            const valorFinal = vfCapital + vfAportes;
            const totalAportado = capital + (aporte * periodo);
            const rendimento = valorFinal - totalAportado;
            
            document.getElementById('ap_valor').textContent = formatCurrency(valorFinal);
            
            document.getElementById('ap_breakdown').innerHTML = `
                <h4>Detalhamento</h4>
                <div class="breakdown-item">
                    <span class="breakdown-label">Capital Inicial:</span>
                    <span class="breakdown-value">${formatCurrency(capital)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Total de Aportes:</span>
                    <span class="breakdown-value">${formatCurrency(aporte * periodo)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Total Investido:</span>
                    <span class="breakdown-value">${formatCurrency(totalAportado)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Rendimento:</span>
                    <span class="breakdown-value">${formatCurrency(rendimento)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Rentabilidade:</span>
                    <span class="breakdown-value">${formatPercent(rendimento / totalAportado * 100)}</span>
                </div>
            `;
            
            document.getElementById('ap_resultado').style.display = 'block';
        }

        function calcularAporteNecessario() {
            const capital = parseFloat(document.getElementById('an_capital').value) || 0;
            const meta = parseFloat(document.getElementById('an_meta').value) || 0;
            const taxa = parseFloat(document.getElementById('an_taxa').value) || 0;
            const periodo = parseInt(document.getElementById('an_periodo').value) || 0;
            
            if (meta <= capital || taxa <= 0 || periodo <= 0) {
                alert('Verifique os valores: a meta deve ser maior que o capital inicial');
                return;
            }
            
            const taxaDecimal = taxa / 100;
            const vfCapital = capital * Math.pow(1 + taxaDecimal, periodo);
            const valorNecessario = meta - vfCapital;
            
            if (valorNecessario <= 0) {
                document.getElementById('an_valor').textContent = 'R$ 0,00';
                document.getElementById('an_resultado').style.display = 'block';
                return;
            }
            
            const aporteNecessario = valorNecessario * taxaDecimal / (Math.pow(1 + taxaDecimal, periodo) - 1);
            
            document.getElementById('an_valor').textContent = formatCurrency(aporteNecessario);
            document.getElementById('an_resultado').style.display = 'block';
        }

        // CÁLCULOS DE DIVIDENDOS

        function calcularDividendYield() {
            const preco = parseFloat(document.getElementById('dy_preco').value) || 0;
            const dividendo = parseFloat(document.getElementById('dy_dividendo').value) || 0;
            
            if (preco <= 0 || dividendo < 0) {
                alert('Por favor, preencha todos os campos com valores válidos');
                return;
            }
            
            const dividendYield = (dividendo / preco) * 100;
            
            document.getElementById('dy_valor').textContent = formatPercent(dividendYield);
            document.getElementById('dy_resultado').style.display = 'block';
        }

        function calcularRendaPassiva() {
            const investimento = parseFloat(document.getElementById('rp_investimento').value) || 0;
            const yield_ = parseFloat(document.getElementById('rp_yield').value) || 0;
            
            if (investimento <= 0 || yield_ <= 0) {
                alert('Por favor, preencha todos os campos com valores positivos');
                return;
            }
            
            const rendaAnual = investimento * (yield_ / 100);
            const rendaMensal = rendaAnual / 12;
            
            document.getElementById('rp_valor').textContent = formatCurrency(rendaMensal);
            
            document.getElementById('rp_breakdown').innerHTML = `
                <h4>Detalhamento</h4>
                <div class="breakdown-item">
                    <span class="breakdown-label">Renda Anual:</span>
                    <span class="breakdown-value">${formatCurrency(rendaAnual)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Renda Mensal:</span>
                    <span class="breakdown-value">${formatCurrency(rendaMensal)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Dividend Yield:</span>
                    <span class="breakdown-value">${formatPercent(yield_)}</span>
                </div>
            `;
            
            document.getElementById('rp_resultado').style.display = 'block';
        }

        // CÁLCULOS DE METAS

        function calcularTempoMeta() {
            const capital = parseFloat(document.getElementById('tm_capital').value) || 0;
            const meta = parseFloat(document.getElementById('tm_meta').value) || 0;
            const aporte = parseFloat(document.getElementById('tm_aporte').value) || 0;
            const taxa = parseFloat(document.getElementById('tm_taxa').value) || 0;
            
            if (meta <= capital || taxa <= 0) {
                alert('Verifique os valores inseridos');
                return;
            }
            
            const taxaDecimal = taxa / 100;
            
            // Calcular tempo usando logaritmos
            let meses;
            
            if (aporte > 0) {
                // Com aportes mensais
                const A = meta;
                const P = capital;
                const PMT = aporte;
                const r = taxaDecimal;
                
                meses = Math.log((A * r + PMT) / (P * r + PMT)) / Math.log(1 + r);
            } else {
                // Apenas juros compostos
                meses = Math.log(meta / capital) / Math.log(1 + taxaDecimal);
            }
            
            const anos = Math.floor(meses / 12);
            const mesesRestantes = Math.round(meses % 12);
            
            let textoTempo = '';
            if (anos > 0) {
                textoTempo += `${anos} ano${anos > 1 ? 's' : ''}`;
                if (mesesRestantes > 0) {
                    textoTempo += ` e ${mesesRestantes} mês${mesesRestantes > 1 ? 'es' : ''}`;
                }
            } else {
                textoTempo = `${Math.round(meses)} mês${Math.round(meses) > 1 ? 'es' : ''}`;
            }
            
            document.getElementById('tm_valor').textContent = textoTempo;
            document.getElementById('tm_resultado').style.display = 'block';
        }

        function calcularTaxaNecessaria() {
            const capital = parseFloat(document.getElementById('tn_capital').value) || 0;
            const meta = parseFloat(document.getElementById('tn_meta').value) || 0;
            const periodo = parseFloat(document.getElementById('tn_periodo').value) || 0;
            
            if (capital <= 0 || meta <= capital || periodo <= 0) {
                alert('Verifique os valores inseridos');
                return;
            }
            
            const taxaNecessaria = (Math.pow(meta / capital, 1 / periodo) - 1) * 100;
            
            document.getElementById('tn_valor').textContent = formatPercent(taxaNecessaria);
            document.getElementById('tn_resultado').style.display = 'block';
        }

        // CÁLCULOS DE AÇÕES

        function calcularPrecoAlvo() {
            const lpa = parseFloat(document.getElementById('pa_lpa').value) || 0;
            const pl = parseFloat(document.getElementById('pa_pl').value) || 0;
            
            if (lpa <= 0 || pl <= 0) {
                alert('Por favor, preencha todos os campos com valores positivos');
                return;
            }
            
            const precoAlvo = lpa * pl;
            
            document.getElementById('pa_valor').textContent = formatCurrency(precoAlvo);
            document.getElementById('pa_resultado').style.display = 'block';
        }

        function calcularRentabilidadeAnualizada() {
            const inicial = parseFloat(document.getElementById('ra_inicial').value) || 0;
            const final = parseFloat(document.getElementById('ra_final').value) || 0;
            const periodo = parseInt(document.getElementById('ra_periodo').value) || 0;
            
            if (inicial <= 0 || final <= 0 || periodo <= 0) {
                alert('Por favor, preencha todos os campos com valores positivos');
                return;
            }
            
            const rentabilidadeTotal = (final / inicial - 1) * 100;
            const rentabilidadeAnualizada = (Math.pow(final / inicial, 365 / periodo) - 1) * 100;
            
            document.getElementById('ra_valor').textContent = formatPercent(rentabilidadeAnualizada);
            
            document.getElementById('ra_breakdown').innerHTML = `
                <h4>Detalhamento</h4>
                <div class="breakdown-item">
                    <span class="breakdown-label">Rentabilidade Total:</span>
                    <span class="breakdown-value">${formatPercent(rentabilidadeTotal)}</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Período:</span>
                    <span class="breakdown-value">${periodo} dias</span>
                </div>
                <div class="breakdown-item">
                    <span class="breakdown-label">Ganho Absoluto:</span>
                    <span class="breakdown-value">${formatCurrency(final - inicial)}</span>
                </div>
            `;
            
            document.getElementById('ra_resultado').style.display = 'block';
        }

        // Adicionar eventos de Enter nos inputs
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.calc-input');
            inputs.forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const button = this.closest('.calc-body').querySelector('.calc-button');
                        if (button) {
                            button.click();
                        }
                    }
                });
            });
        });
    </script>
</body>
</html> 