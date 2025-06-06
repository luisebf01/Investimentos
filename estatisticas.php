<?php
/**
 * ESTATÍSTICAS MENSAIS - Métricas de desempenho para entregadores
 * 
 * Esta página permite ao entregador acompanhar:
 * - Métricas de produtividade
 * - Estatísticas de entregas
 * - Comparativos de desempenho
 * - Tendências e evolução
 * - Análise de horários mais produtivos
 */

// Incluir classes necessárias
require_once 'classes/Auth.php';

// Criar instâncias das classes
$auth = new Auth();

// VERIFICAR SE USUÁRIO ESTÁ LOGADO
$auth->requireLogin();

// OBTER DADOS DO USUÁRIO ATUAL
$user = $auth->getCurrentUser();

// DADOS SIMULADOS DE ESTATÍSTICAS (aqui você implementará a lógica real)
$mes_atual = date('m');
$ano_atual = date('Y');
$nome_mes = date('F', mktime(0, 0, 0, $mes_atual, 1, $ano_atual));
$nome_mes_pt = [
    'January' => 'Janeiro', 'February' => 'Fevereiro', 'March' => 'Março',
    'April' => 'Abril', 'May' => 'Maio', 'June' => 'Junho',
    'July' => 'Julho', 'August' => 'Agosto', 'September' => 'Setembro',
    'October' => 'Outubro', 'November' => 'Novembro', 'December' => 'Dezembro'
][$nome_mes];

// ESTATÍSTICAS GERAIS (simulado)
$total_entregas = 210;
$dias_trabalhados = 18;
$horas_trabalhadas = 144; // 8h por dia
$km_rodados = 1250;
$entregas_por_dia = $dias_trabalhados > 0 ? $total_entregas / $dias_trabalhados : 0;
$entregas_por_hora = $horas_trabalhadas > 0 ? $total_entregas / $horas_trabalhadas : 0;
$km_por_entrega = $total_entregas > 0 ? $km_rodados / $total_entregas : 0;

// ESTATÍSTICAS DE QUALIDADE (simulado)
$avaliacoes_positivas = 195;
$avaliacoes_negativas = 8;
$avaliacoes_neutras = 7;
$taxa_aprovacao = $total_entregas > 0 ? ($avaliacoes_positivas / $total_entregas) * 100 : 0;
$tempo_medio_entrega = 25; // minutos
$entregas_no_prazo = 198;
$taxa_pontualidade = $total_entregas > 0 ? ($entregas_no_prazo / $total_entregas) * 100 : 0;

// DISTRIBUIÇÃO POR PERÍODO DO DIA (simulado)
$periodo_distribuicao = [
    ['periodo' => 'Manhã (6h-12h)', 'entregas' => 68, 'faturamento' => 925.50],
    ['periodo' => 'Tarde (12h-18h)', 'entregas' => 89, 'faturamento' => 1245.80],
    ['periodo' => 'Noite (18h-24h)', 'entregas' => 53, 'faturamento' => 685.10]
];

// DISTRIBUIÇÃO POR DIA DA SEMANA (simulado)
$semana_distribuicao = [
    ['dia' => 'Segunda', 'entregas' => 28, 'faturamento' => 385.20],
    ['dia' => 'Terça', 'entregas' => 32, 'faturamento' => 445.60],
    ['dia' => 'Quarta', 'entregas' => 30, 'faturamento' => 412.30],
    ['dia' => 'Quinta', 'entregas' => 35, 'faturamento' => 478.90],
    ['dia' => 'Sexta', 'entregas' => 38, 'faturamento' => 521.75],
    ['dia' => 'Sábado', 'entregas' => 31, 'faturamento' => 425.80],
    ['dia' => 'Domingo', 'entregas' => 16, 'faturamento' => 186.85]
];

// COMPARATIVO COM O MÊS ANTERIOR (simulado)
$mes_anterior = [
    'entregas' => 185,
    'faturamento' => 2456.30,
    'dias_trabalhados' => 16,
    'horas_trabalhadas' => 128
];

$crescimento_entregas = $mes_anterior['entregas'] > 0 ? (($total_entregas - $mes_anterior['entregas']) / $mes_anterior['entregas']) * 100 : 0;
$crescimento_produtividade = $mes_anterior['horas_trabalhadas'] > 0 ? 
    ((($total_entregas / $horas_trabalhadas) - ($mes_anterior['entregas'] / $mes_anterior['horas_trabalhadas'])) / 
     ($mes_anterior['entregas'] / $mes_anterior['horas_trabalhadas'])) * 100 : 0;

// TOP DIAS DO MÊS (simulado)
$top_dias = [
    ['data' => '15/12', 'entregas' => 18, 'faturamento' => 245.60, 'horas' => 9],
    ['data' => '22/12', 'entregas' => 17, 'faturamento' => 232.40, 'horas' => 8.5],
    ['data' => '08/12', 'entregas' => 16, 'faturamento' => 218.75, 'horas' => 8],
    ['data' => '29/12', 'entregas' => 15, 'faturamento' => 205.90, 'horas' => 7.5],
    ['data' => '12/12', 'entregas' => 15, 'faturamento' => 198.30, 'horas' => 8]
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estatísticas - Sistema de Entregas</title>
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
                <li><a href="balanco.php">Balanço</a></li>
                <li><a href="estatisticas.php" class="active">Estatísticas</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <div class="page-header" style="margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>📊 Estatísticas</h1>
                    <p class="text-secondary">Análise de desempenho e produtividade - <?php echo $nome_mes_pt; ?> <?php echo $ano_atual; ?></p>
                </div>
            </div>
            
            <!-- CARDS DE MÉTRICAS DE PRODUTIVIDADE -->
            <div class="stats-row">
                <!-- Card: Entregas por Dia -->
                <div class="stat-card">
                    <h3>📦 Entregas/Dia</h3>
                    <div class="stat-value positive"><?php echo number_format($entregas_por_dia, 1, ',', '.'); ?></div>
                    <div class="stat-label">Média diária</div>
                </div>
                
                <!-- Card: Entregas por Hora -->
                <div class="stat-card">
                    <h3>⏱️ Entregas/Hora</h3>
                    <div class="stat-value positive"><?php echo number_format($entregas_por_hora, 1, ',', '.'); ?></div>
                    <div class="stat-label">Produtividade</div>
                </div>
                
                <!-- Card: Taxa de Aprovação -->
                <div class="stat-card">
                    <h3>⭐ Aprovação</h3>
                    <div class="stat-value <?php echo $taxa_aprovacao >= 90 ? 'positive' : ($taxa_aprovacao >= 80 ? 'neutral' : 'negative'); ?>">
                        <?php echo number_format($taxa_aprovacao, 1, ',', '.'); ?>%
                    </div>
                    <div class="stat-label"><?php echo $avaliacoes_positivas; ?> avaliações +</div>
                </div>
                
                <!-- Card: Pontualidade -->
                <div class="stat-card">
                    <h3>🎯 Pontualidade</h3>
                    <div class="stat-value <?php echo $taxa_pontualidade >= 95 ? 'positive' : ($taxa_pontualidade >= 85 ? 'neutral' : 'negative'); ?>">
                        <?php echo number_format($taxa_pontualidade, 1, ',', '.'); ?>%
                    </div>
                    <div class="stat-label"><?php echo $entregas_no_prazo; ?> no prazo</div>
                </div>
            </div>
            
            <!-- MÉTRICAS DETALHADAS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- MÉTRICAS OPERACIONAIS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🚗 Métricas Operacionais</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px; text-align: center;">
                                <div style="color: #ccc; font-size: 0.9rem;">KM Rodados</div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #45B7D1;">
                                    <?php echo number_format($km_rodados, 0, ',', '.'); ?>
                                </div>
                            </div>
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px; text-align: center;">
                                <div style="color: #ccc; font-size: 0.9rem;">KM/Entrega</div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #45B7D1;">
                                    <?php echo number_format($km_por_entrega, 1, ',', '.'); ?>
                                </div>
                            </div>
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px; text-align: center;">
                                <div style="color: #ccc; font-size: 0.9rem;">Horas Trabalhadas</div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #FFA726;">
                                    <?php echo $horas_trabalhadas; ?>h
                                </div>
                            </div>
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px; text-align: center;">
                                <div style="color: #ccc; font-size: 0.9rem;">Tempo Médio</div>
                                <div style="font-size: 1.5rem; font-weight: bold; color: #FFA726;">
                                    <?php echo $tempo_medio_entrega; ?>min
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- COMPARATIVO COM MÊS ANTERIOR -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📈 Comparativo</h2>
                    </div>
                    <div class="card-body">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #ccc;">Entregas</span>
                                    <div style="text-align: right;">
                                        <div style="font-weight: bold;"><?php echo $total_entregas; ?> vs <?php echo $mes_anterior['entregas']; ?></div>
                                        <small class="<?php echo $crescimento_entregas >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $crescimento_entregas >= 0 ? '+' : ''; ?><?php echo number_format($crescimento_entregas, 1, ',', '.'); ?>%
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #ccc;">Produtividade</span>
                                    <div style="text-align: right;">
                                        <div style="font-weight: bold;">
                                            <?php echo number_format($entregas_por_hora, 1, ',', '.'); ?> vs 
                                            <?php echo number_format($mes_anterior['entregas'] / $mes_anterior['horas_trabalhadas'], 1, ',', '.'); ?>
                                        </div>
                                        <small class="<?php echo $crescimento_produtividade >= 0 ? 'positive' : 'negative'; ?>">
                                            <?php echo $crescimento_produtividade >= 0 ? '+' : ''; ?><?php echo number_format($crescimento_produtividade, 1, ',', '.'); ?>%
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px;">
                                <div style="color: #ccc; font-size: 0.9rem; margin-bottom: 0.5rem;">Evolução Geral</div>
                                <div style="font-size: 1.2rem; font-weight: bold; color: <?php echo ($crescimento_entregas > 0 && $crescimento_produtividade > 0) ? '#00C851' : '#FFA500'; ?>;">
                                    <?php 
                                    if ($crescimento_entregas > 0 && $crescimento_produtividade > 0) {
                                        echo "📈 Crescimento";
                                    } elseif ($crescimento_entregas > 0 || $crescimento_produtividade > 0) {
                                        echo "📊 Estável";
                                    } else {
                                        echo "📉 Declínio";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- DISTRIBUIÇÕES E TOP PERFORMANCE -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
                <!-- DISTRIBUIÇÃO POR PERÍODO -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🕐 Por Período do Dia</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Período</th>
                                        <th>Entregas</th>
                                        <th>Média/h</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($periodo_distribuicao as $periodo): ?>
                                        <?php $media_hora = $periodo['entregas'] / 6; // 6 horas por período ?>
                                        <tr>
                                            <td><?php echo $periodo['periodo']; ?></td>
                                            <td><?php echo $periodo['entregas']; ?></td>
                                            <td><?php echo number_format($media_hora, 1, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- TOP 5 MELHORES DIAS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🏆 Top 5 Melhores Dias</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Entregas</th>
                                        <th>Horas</th>
                                        <th>Ent/h</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_dias as $index => $dia): ?>
                                        <?php $produtividade = $dia['horas'] > 0 ? $dia['entregas'] / $dia['horas'] : 0; ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                    <span style="color: gold; font-weight: bold;">#<?php echo $index + 1; ?></span>
                                                    <?php echo $dia['data']; ?>
                                                </div>
                                            </td>
                                            <td style="font-weight: bold;"><?php echo $dia['entregas']; ?></td>
                                            <td><?php echo $dia['horas']; ?>h</td>
                                            <td class="positive"><?php echo number_format($produtividade, 1, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- DISTRIBUIÇÃO POR DIA DA SEMANA -->
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h2 class="card-title">📅 Distribuição por Dia da Semana</h2>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Dia da Semana</th>
                                    <th>Entregas</th>
                                    <th>Faturamento</th>
                                    <th>Média/Entrega</th>
                                    <th>% do Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semana_distribuicao as $dia): ?>
                                    <?php 
                                    $media_entrega = $dia['entregas'] > 0 ? $dia['faturamento'] / $dia['entregas'] : 0;
                                    $percentual = $total_entregas > 0 ? ($dia['entregas'] / $total_entregas) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td style="font-weight: bold;"><?php echo $dia['dia']; ?></td>
                                        <td><?php echo $dia['entregas']; ?></td>
                                        <td class="positive">R$ <?php echo number_format($dia['faturamento'], 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($media_entrega, 2, ',', '.'); ?></td>
                                        <td><?php echo number_format($percentual, 1, ',', '.'); ?>%</td>
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