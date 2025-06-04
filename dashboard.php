<?php
/**
 * DASHBOARD - PÁGINA PRINCIPAL DO SISTEMA
 * 
 * Esta é a página principal que o usuário vê após fazer login.
 * Mostra um resumo geral de todos os investimentos:
 * - Total investido
 * - Valor atual da carteira
 * - Rendimento (ganho/perda)
 * - Lista dos investimentos
 * - Gráficos por categoria
 * 
 * FUNCIONALIDADES:
 * 1. Verificar se usuário está logado
 * 2. Buscar resumo financeiro
 * 3. Buscar lista de investimentos
 * 4. Calcular estatísticas
 * 5. Exibir gráficos visuais
 */

// Incluir classes necessárias
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

// Criar instâncias das classes
$auth = new Auth();
$investment = new Investment();

// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// Redirecionar para login se não estiver
$auth->requireLogin();

// OBTER DADOS DO USUÁRIO ATUAL
// getCurrentUser() retorna array com id, nome, email
$user = $auth->getCurrentUser();

// BUSCAR RESUMO FINANCEIRO GERAL
// getSummaryByUser() retorna totais calculados
$summary = $investment->getSummaryByUser($user['id']);

// BUSCAR LISTA DE INVESTIMENTOS DO USUÁRIO
// getAllByUser() retorna array com todos os investimentos
$investments = $investment->getAllByUser($user['id']);

// BUSCAR RESUMO POR TIPO DE INVESTIMENTO
// getSummaryByType() retorna dados para gráfico de pizza
$summaryByType = $investment->getSummaryByType($user['id']);

// TRATAMENTO DE DADOS NULOS
// Se não há investimentos, definir valores padrão zero
if (!$summary['total_investido']) {
    $summary['total_investido'] = 0;
    $summary['total_atual'] = 0;
    $summary['total_rendimento'] = 0;
    $summary['total_investimentos'] = 0;
}

// CALCULAR PERCENTUAL DE RENDIMENTO GERAL
// Evitar divisão por zero
$percentual_geral = $summary['total_investido'] > 0 ? 
    ($summary['total_rendimento'] / $summary['total_investido']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <script src="assets/js/stock-ticker.js"></script>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="add_investment.php">Adicionar</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p class="text-secondary">Visão geral dos seus investimentos</p>
                </div>
                <a href="add_investment.php" class="btn btn-primary">
                    + Adicionar Investimento
                </a>
            </div>
            
            <!-- CARDS DE RESUMO FINANCEIRO -->
            <div class="stats-row">
                <!-- Card: Total Investido -->
                <div class="stat-card">
                    <h3>Total Investido</h3>
                    <div class="stat-value neutral">R$ <?php echo number_format($summary['total_investido'], 2, ',', '.'); ?></div>
                    <div class="stat-label"><?php echo $summary['total_investimentos']; ?> investimentos</div>
                </div>
                
                <!-- Card: Valor Atual -->
                <div class="stat-card">
                    <h3>Valor Atual</h3>
                    <div class="stat-value neutral">R$ <?php echo number_format($summary['total_atual'], 2, ',', '.'); ?></div>
                    <div class="stat-label">Patrimônio total</div>
                </div>
                
                <!-- Card: Rendimento -->
                <div class="stat-card">
                    <h3>Rendimento</h3>
                    <div class="stat-value <?php echo $summary['total_rendimento'] >= 0 ? 'positive' : 'negative'; ?>">
                        R$ <?php echo number_format($summary['total_rendimento'], 2, ',', '.'); ?>
                    </div>
                    <div class="stat-label <?php echo $percentual_geral >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $percentual_geral >= 0 ? '+' : ''; ?><?php echo number_format($percentual_geral, 2, ',', '.'); ?>%
                    </div>
                </div>
                
                <!-- Card: Rentabilidade -->
                <div class="stat-card">
                    <h3>Rentabilidade</h3>
                    <div class="stat-value <?php echo $percentual_geral >= 0 ? 'positive' : 'negative'; ?>">
                        <?php echo $percentual_geral >= 0 ? '+' : ''; ?><?php echo number_format($percentual_geral, 2, ',', '.'); ?>%
                    </div>
                    <div class="stat-label">No período total</div>
                </div>
            </div>
            
            <!-- GRID DE CONTEÚDO -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <!-- SEÇÃO: LISTA DE INVESTIMENTOS -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">💼 Seus Investimentos</h2>
                    </div>
                    <div class="card-body">
                    
                    <?php if (count($investments) > 0): ?>
                        <!-- TABELA COM INVESTIMENTOS -->
                        <div class="table-container">
                        <table class="table">
                            <!-- Cabeçalho da tabela -->
                            <thead>
                                <tr>
                                    <th>Investimento</th>
                                    <th>Tipo</th>
                                    <th>Valor Investido</th>
                                    <th>Valor Atual</th>
                                    <th>Rendimento</th>
                                </tr>
                            </thead>
                            <!-- Corpo da tabela -->
                            <tbody>
                                <?php foreach ($investments as $inv): ?>
                                    <tr>
                                        <!-- Coluna: Nome e Ticker -->
                                        <td>
                                            <div class="text-primary-theme" style="font-weight: 600;"><?php echo htmlspecialchars($inv['nome']); ?></div>
                                            <?php if ($inv['ticker']): ?>
                                                <div style="color: #ccc; font-size: 0.9rem;"><?php echo htmlspecialchars($inv['ticker']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- Coluna: Tipo -->
                                        <td>
                                            <span class="badge" style="background-color: <?php echo htmlspecialchars($inv['tipo_cor']); ?>">
                                                <?php echo htmlspecialchars($inv['tipo_nome']); ?>
                                            </span>
                                        </td>
                                        
                                        <!-- Coluna: Valor Investido -->
                                        <td>R$ <?php echo number_format($inv['valor_investido'], 2, ',', '.'); ?></td>
                                        
                                        <!-- Coluna: Valor Atual -->
                                        <td>R$ <?php echo number_format($inv['valor_atual'], 2, ',', '.'); ?></td>
                                        
                                        <!-- Coluna: Rendimento -->
                                        <td class="<?php echo $inv['rendimento'] >= 0 ? 'positive' : 'negative'; ?>">
                                            R$ <?php echo number_format($inv['rendimento'], 2, ',', '.'); ?>
                                            <br>
                                            <small>
                                                <?php echo $inv['percentual_rendimento'] >= 0 ? '+' : ''; ?>
                                                <?php echo number_format($inv['percentual_rendimento'], 2, ',', '.'); ?>%
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <!-- ESTADO VAZIO: Quando não há investimentos -->
                        <div style="text-align: center; padding: 3rem; color: #ccc;">
                            <h3>📈 Ainda não há investimentos</h3>
                            <p>Comece adicionando seu primeiro investimento para acompanhar sua carteira.</p>
                            <br>
                            <a href="add_investment.php" class="btn btn-primary">Adicionar Primeiro Investimento</a>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
                
                <!-- SEÇÃO: DISTRIBUIÇÃO POR TIPO -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📊 Distribuição por Tipo</h2>
                    </div>
                    <div class="card-body">
                    
                    <?php if (count($summaryByType) > 0): ?>
                        <!-- LISTA COM DISTRIBUIÇÃO -->
                        <?php foreach ($summaryByType as $type): ?>
                            <?php 
                            // Calcular percentual deste tipo em relação ao total
                            $percentual = $summary['total_atual'] > 0 ? 
                                ($type['valor_atual'] / $summary['total_atual']) * 100 : 0; 
                            ?>
                            <div style="padding: 0.75rem 0; border-bottom: 1px solid #333;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <div>
                                        <strong class="text-primary-theme"><?php echo htmlspecialchars($type['tipo']); ?></strong>
                                        <div style="color: #ccc; font-size: 0.9rem;"><?php echo $type['quantidade']; ?> investimento(s)</div>
                                    </div>
                                    <!-- Valor e percentual -->
                                    <div style="text-align: right;">
                                        <div style="font-weight: 600;">R$ <?php echo number_format($type['valor_atual'], 2, ',', '.'); ?></div>
                                        <small style="color: #ccc;"><?php echo number_format($percentual, 1, ',', '.'); ?>%</small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- ESTADO VAZIO -->
                        <div style="text-align: center; padding: 2rem; color: #ccc;">
                            <p>📊 Nenhum dado para exibir</p>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/**
 * RESUMO DO ARQUIVO dashboard.php:
 * 
 * FUNCIONALIDADES PRINCIPAIS:
 * 1. Verificar autenticação do usuário
 * 2. Buscar dados do usuário atual
 * 3. Calcular resumo financeiro geral
 * 4. Listar investimentos em tabela
 * 5. Mostrar distribuição por tipos
 * 6. Exibir estatísticas em cards
 * 
 * DADOS EXIBIDOS:
 * - Total investido (soma de todo dinheiro aplicado)
 * - Valor atual (valor atual de toda carteira)
 * - Rendimento (ganho/perda em R$ e %)
 * - Lista detalhada de investimentos
 * - Distribuição por tipo (Ações, FIIs, etc.)
 * 
 * ELEMENTOS VISUAIS:
 * - Cards de resumo com cores indicativas
 * - Tabela responsiva com investimentos
 * - Navegação superior com menu
 * - Gráfico de distribuição por tipo
 * - Estados vazios quando não há dados
 * 
 * SEGURANÇA:
 * - Verificação de login obrigatória
 * - Dados filtrados por usuário
 * - htmlspecialchars() para prevenir XSS
 * - number_format() para exibir valores
 * 
 * RESPONSIVIDADE:
 * - Layout adaptável para mobile
 * - Grids flexíveis
 * - Menu colapsável
 */
?> 