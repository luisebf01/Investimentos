<?php
/**
 * FATURAMENTO MENSAL - Controle de faturamento para entregadores
 * 
 * Esta página permite ao entregador acompanhar:
 * - Faturamento diário, semanal e mensal
 * - Número de entregas realizadas
 * - Valor médio por entrega
 * - Comparativos entre períodos
 * - Meta de faturamento mensal
 */

// Incluir classes necessárias
require_once 'classes/Auth.php';

// Criar instâncias das classes
$auth = new Auth();

// VERIFICAR SE USUÁRIO ESTÁ LOGADO
$auth->requireLogin();

// OBTER DADOS DO USUÁRIO ATUAL
$user = $auth->getCurrentUser();

// DADOS SIMULADOS DE FATURAMENTO (aqui você implementará a lógica real)
$mes_atual = date('m');
$ano_atual = date('Y');
$nome_mes = date('F', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
$nome_mes_pt = [
    'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
    'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
    'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
    'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
][$nome_mes];

// Dados simulados (substituir pela sua lógica de banco de dados)
$faturamento_hoje = 145.50;
$entregas_hoje = 12;
$faturamento_semana = 980.75;
$entregas_semana = 78;
$faturamento_mes = 2856.40;
$entregas_mes = 210;
$meta_mensal = 3500.00;
$valor_medio_entrega = $entregas_mes > 0 ? $faturamento_mes / $entregas_mes : 0;
$percentual_meta = $meta_mensal > 0 ? ($faturamento_mes / $meta_mensal) * 100 : 0;

// Dados de faturamento dos últimos 7 dias (simulado)
$ultimos_dias = [
    ['data' => date('Y-m-d', strtotime('-6 days')), 'faturamento' => 128.50, 'entregas' => 10],
    ['data' => date('Y-m-d', strtotime('-5 days')), 'faturamento' => 156.75, 'entregas' => 13],
    ['data' => date('Y-m-d', strtotime('-4 days')), 'faturamento' => 134.20, 'entregas' => 11],
    ['data' => date('Y-m-d', strtotime('-3 days')), 'faturamento' => 189.30, 'entregas' => 15],
    ['data' => date('Y-m-d', strtotime('-2 days')), 'faturamento' => 167.45, 'entregas' => 14],
    ['data' => date('Y-m-d', strtotime('-1 day')), 'faturamento' => 198.60, 'entregas' => 16],
    ['data' => date('Y-m-d'), 'faturamento' => $faturamento_hoje, 'entregas' => $entregas_hoje]
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faturamento - Sistema de Entregas</title>
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
                <li><a href="faturamento.php" class="active">Faturamento</a></li>
                <li><a href="balanco.php">Balanço</a></li>
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
                    <h1>📊 Faturamento</h1>
                    <p class="text-secondary">Acompanhe seu faturamento como entregador - <?php echo $nome_mes_pt; ?> <?php echo $ano_atual; ?></p>
                </div>
            </div>
            
            <!-- CARDS DE RESUMO FINANCEIRO -->
            <div class="stats-row">
                <!-- Card: Faturamento Hoje -->
                <div class="stat-card">
                    <h3>💰 Hoje</h3>
                    <div class="stat-value positive">R$ <?php echo number_format($faturamento_hoje, 2, ',', '.'); ?></div>
                    <div class="stat-label"><?php echo $entregas_hoje; ?> entregas</div>
                </div>
                
                <!-- Card: Faturamento Semana -->
                <div class="stat-card">
                    <h3>📅 Esta Semana</h3>
                    <div class="stat-value positive">R$ <?php echo number_format($faturamento_semana, 2, ',', '.'); ?></div>
                    <div class="stat-label"><?php echo $entregas_semana; ?> entregas</div>
                </div>
                
                <!-- Card: Faturamento Mês -->
                <div class="stat-card">
                    <h3>🗓️ Este Mês</h3>
                    <div class="stat-value positive">R$ <?php echo number_format($faturamento_mes, 2, ',', '.'); ?></div>
                    <div class="stat-label"><?php echo $entregas_mes; ?> entregas</div>
                </div>
                
                <!-- Card: Meta Mensal -->
                <div class="stat-card">
                    <h3>🎯 Meta Mensal</h3>
                    <div class="stat-value <?php echo $percentual_meta >= 80 ? 'positive' : 'neutral'; ?>">
                        <?php echo number_format($percentual_meta, 1, ',', '.'); ?>%
                    </div>
                    <div class="stat-label">R$ <?php echo number_format($meta_mensal, 2, ',', '.'); ?></div>
                </div>
            </div>
            
            <!-- GRID DE CONTEÚDO -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- SEÇÃO: FATURAMENTO DOS ÚLTIMOS 7 DIAS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📈 Últimos 7 Dias</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Faturamento</th>
                                        <th>Entregas</th>
                                        <th>Média/Entrega</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos_dias as $dia): ?>
                                        <?php 
                                        $media_dia = $dia['entregas'] > 0 ? $dia['faturamento'] / $dia['entregas'] : 0;
                                        $data_formatada = date('d/m', strtotime($dia['data']));
                                        $dia_semana = date('w', strtotime($dia['data']));
                                        $nomes_dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                                        ?>
                                        <tr>
                                            <td>
                                                <div><?php echo $data_formatada; ?></div>
                                                <small style="color: #ccc;"><?php echo $nomes_dias[$dia_semana]; ?></small>
                                            </td>
                                            <td class="positive">R$ <?php echo number_format($dia['faturamento'], 2, ',', '.'); ?></td>
                                            <td><?php echo $dia['entregas']; ?></td>
                                            <td>R$ <?php echo number_format($media_dia, 2, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- SEÇÃO: RESUMO E ESTATÍSTICAS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📋 Resumo</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <!-- Valor médio por entrega -->
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="color: #ccc; font-size: 0.9rem;">Valor Médio/Entrega</div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #00C851;">
                                    R$ <?php echo number_format($valor_medio_entrega, 2, ',', '.'); ?>
                                </div>
                            </div>
                            
                            <!-- Progresso da meta -->
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="color: #ccc; font-size: 0.9rem; margin-bottom: 0.5rem;">Progresso da Meta</div>
                                <div style="background: #333; border-radius: 10px; height: 10px; overflow: hidden;">
                                    <div style="background: <?php echo $percentual_meta >= 80 ? '#00C851' : '#FFA500'; ?>; height: 100%; width: <?php echo min($percentual_meta, 100); ?>%; transition: width 0.3s;"></div>
                                </div>
                                <div style="font-size: 0.9rem; color: #ccc; margin-top: 0.5rem;">
                                    Faltam R$ <?php echo number_format(max(0, $meta_mensal - $faturamento_mes), 2, ',', '.'); ?>
                                </div>
                            </div>
                            
                            <!-- Dias restantes no mês -->
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="color: #ccc; font-size: 0.9rem;">Dias Restantes</div>
                                <div style="font-size: 1.2rem; font-weight: bold;">
                                    <?php echo date('t') - date('j'); ?> dias
                                </div>
                            </div>
                            
                            <!-- Projeção mensal -->
                            <?php 
                            $dias_trabalhados = date('j');
                            $projecao = $dias_trabalhados > 0 ? ($faturamento_mes / $dias_trabalhados) * date('t') : 0;
                            ?>
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="color: #ccc; font-size: 0.9rem;">Projeção Mensal</div>
                                <div style="font-size: 1.2rem; font-weight: bold; color: <?php echo $projecao >= $meta_mensal ? '#00C851' : '#FFA500'; ?>;">
                                    R$ <?php echo number_format($projecao, 2, ',', '.'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
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