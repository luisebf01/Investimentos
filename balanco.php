<?php
/**
 * BALANÇO MENSAL - Controle de receitas e despesas para entregadores
 * 
 * Esta página permite ao entregador acompanhar:
 * - Receitas (faturamento total)
 * - Despesas (combustível, manutenção, taxas)
 * - Lucro líquido
 * - Comparativo mensal
 * - Margem de lucro
 */

// Incluir classes necessárias
require_once 'classes/Auth.php';

// Criar instâncias das classes
$auth = new Auth();

// VERIFICAR SE USUÁRIO ESTÁ LOGADO
$auth->requireLogin();

// OBTER DADOS DO USUÁRIO ATUAL
$user = $auth->getCurrentUser();

// DADOS SIMULADOS DE BALANÇO (aqui você implementará a lógica real)
$mes_atual = date('m');
$ano_atual = date('Y');
$nome_mes = date('F', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
$nome_mes_pt = [
    'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
    'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
    'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
    'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
][$nome_mes];

// RECEITAS (simulado)
$faturamento_mes = 2856.40;
$bonus_entregas = 180.50;
$gorjetas = 125.30;
$receita_total = $faturamento_mes + $bonus_entregas + $gorjetas;

// DESPESAS (simulado)
$combustivel = 450.75;
$manutencao = 120.00;
$taxa_app = 285.64; // 10% do faturamento
$ipva_seguro = 85.50;
$outras_despesas = 65.20;
$despesa_total = $combustivel + $manutencao + $taxa_app + $ipva_seguro + $outras_despesas;

// CÁLCULOS
$lucro_liquido = $receita_total - $despesa_total;
$margem_lucro = $receita_total > 0 ? ($lucro_liquido / $receita_total) * 100 : 0;

// Dados dos últimos 6 meses (simulado)
$historico_meses = [
    ['mes' => 'Jul/24', 'receita' => 2750.80, 'despesa' => 980.50, 'lucro' => 1770.30],
    ['mes' => 'Ago/24', 'receita' => 2920.15, 'despesa' => 1050.75, 'lucro' => 1869.40],
    ['mes' => 'Set/24', 'receita' => 2680.90, 'despesa' => 945.20, 'lucro' => 1735.70],
    ['mes' => 'Out/24', 'receita' => 3150.60, 'despesa' => 1125.80, 'lucro' => 2024.80],
    ['mes' => 'Nov/24', 'receita' => 2890.45, 'despesa' => 995.30, 'lucro' => 1895.15],
    ['mes' => 'Dez/24', 'receita' => $receita_total, 'despesa' => $despesa_total, 'lucro' => $lucro_liquido]
];

// Categorias de despesas detalhadas
$categorias_despesas = [
    ['categoria' => 'Combustível', 'valor' => $combustivel, 'cor' => '#FF6B6B'],
    ['categoria' => 'Taxa do App', 'valor' => $taxa_app, 'cor' => '#4ECDC4'],
    ['categoria' => 'Manutenção', 'valor' => $manutencao, 'cor' => '#45B7D1'],
    ['categoria' => 'IPVA/Seguro', 'valor' => $ipva_seguro, 'cor' => '#FFA726'],
    ['categoria' => 'Outras', 'valor' => $outras_despesas, 'cor' => '#AB47BC']
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balanço - Sistema de Entregas</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><button onclick="openAddInvestmentModal()" class="nav-button">Adicionar</button></li>
                <li><a href="faturamento.php">Faturamento</a></li>
                <li><a href="balanco.php" class="active">Balanço</a></li>
                <li><a href="estatisticas.php">Estatísticas</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <div class="page-header" style="margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>💰 Balanço</h1>
                    <p class="text-secondary">Controle suas receitas e despesas - <?php echo $nome_mes_pt; ?> <?php echo $ano_atual; ?></p>
                </div>
            </div>
            
            <!-- CARDS DE RESUMO FINANCEIRO -->
            <div class="stats-row">
                <!-- Card: Receita Total -->
                <div class="stat-card">
                    <h3>💵 Receita Total</h3>
                    <div class="stat-value positive">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></div>
                    <div class="stat-label">Faturamento + Extras</div>
                </div>
                
                <!-- Card: Despesa Total -->
                <div class="stat-card">
                    <h3>📉 Despesa Total</h3>
                    <div class="stat-value negative">R$ <?php echo number_format($despesa_total, 2, ',', '.'); ?></div>
                    <div class="stat-label">Custos operacionais</div>
                </div>
                
                <!-- Card: Lucro Líquido -->
                <div class="stat-card">
                    <h3>💎 Lucro Líquido</h3>
                    <div class="stat-value <?php echo $lucro_liquido >= 0 ? 'positive' : 'negative'; ?>">
                        R$ <?php echo number_format($lucro_liquido, 2, ',', '.'); ?>
                    </div>
                    <div class="stat-label">Receita - Despesas</div>
                </div>
                
                <!-- Card: Margem de Lucro -->
                <div class="stat-card">
                    <h3>📊 Margem</h3>
                    <div class="stat-value <?php echo $margem_lucro >= 50 ? 'positive' : ($margem_lucro >= 30 ? 'neutral' : 'negative'); ?>">
                        <?php echo number_format($margem_lucro, 1, ',', '.'); ?>%
                    </div>
                    <div class="stat-label">Margem de lucro</div>
                </div>
            </div>
            
            <!-- DETALHAMENTO RECEITAS E DESPESAS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- RECEITAS DETALHADAS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">💰 Receitas</h2>
                    </div>
                    <div class="card-body">
                        <div class="stats-list">
                            <div class="stat-item">
                                <span class="stat-label">Faturamento Entregas</span>
                                <span class="stat-value positive">R$ <?php echo number_format($faturamento_mes, 2, ',', '.'); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Bônus/Incentivos</span>
                                <span class="stat-value positive">R$ <?php echo number_format($bonus_entregas, 2, ',', '.'); ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Gorjetas</span>
                                <span class="stat-value positive">R$ <?php echo number_format($gorjetas, 2, ',', '.'); ?></span>
                            </div>
                            <div class="stat-item" style="border-top: 2px solid #333; font-weight: bold;">
                                <span class="stat-label">Total Receitas</span>
                                <span class="stat-value positive">R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- DESPESAS DETALHADAS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📊 Despesas por Categoria</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <?php foreach ($categorias_despesas as $categoria): ?>
                                <?php $percentual = $despesa_total > 0 ? ($categoria['valor'] / $despesa_total) * 100 : 0; ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: <?php echo $categoria['cor']; ?>;"></div>
                                        <span><?php echo $categoria['categoria']; ?></span>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: bold;">R$ <?php echo number_format($categoria['valor'], 2, ',', '.'); ?></div>
                                        <small style="color: #ccc;"><?php echo number_format($percentual, 1, ',', '.'); ?>%</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div style="border-top: 2px solid #333; padding-top: 1rem; margin-top: 0.5rem;">
                                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                                    <span>Total Despesas</span>
                                    <span class="negative">R$ <?php echo number_format($despesa_total, 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- HISTÓRICO DOS ÚLTIMOS MESES -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">📈 Histórico - Últimos 6 Meses</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mês</th>
                                    <th>Receita</th>
                                    <th>Despesa</th>
                                    <th>Lucro Líquido</th>
                                    <th>Margem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico_meses as $mes): ?>
                                    <?php 
                                    $margem_mes = $mes['receita'] > 0 ? ($mes['lucro'] / $mes['receita']) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td style="font-weight: bold;"><?php echo $mes['mes']; ?></td>
                                        <td class="positive">R$ <?php echo number_format($mes['receita'], 2, ',', '.'); ?></td>
                                        <td class="negative">R$ <?php echo number_format($mes['despesa'], 2, ',', '.'); ?></td>
                                        <td class="<?php echo $mes['lucro'] >= 0 ? 'positive' : 'negative'; ?>">
                                            R$ <?php echo number_format($mes['lucro'], 2, ',', '.'); ?>
                                        </td>
                                        <td class="<?php echo $margem_mes >= 50 ? 'positive' : ($margem_mes >= 30 ? 'neutral' : 'negative'); ?>">
                                            <?php echo number_format($margem_mes, 1, ',', '.'); ?>%
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .stats-list {
        display: flex;
        flex-direction: column;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #333;
        padding: 0.75rem 0;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: #ccc;
        font-weight: 500;
    }
    
    .stat-value {
        font-weight: bold;
    }

    /* Botão do menu que parece link */
    .nav-button {
        background: none;
        border: none;
        color: #ccc;
        font-size: 1rem;
        font-family: inherit;
        cursor: pointer;
        padding: 0;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .nav-button:hover {
        color: var(--primary-light);
    }
    </style>

    <script>
    // Função para abrir modal (será implementada na página que incluir o modal)
    function openAddInvestmentModal() {
        // Redirecionar temporariamente até que todas as páginas tenham o modal
        window.location.href = 'dashboard.php';
        setTimeout(() => {
            if (typeof window.openAddInvestmentModal === 'function') {
                window.openAddInvestmentModal();
            }
        }, 100);
    }
    </script>
</body>
</html> 