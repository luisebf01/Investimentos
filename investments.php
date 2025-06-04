<?php
/**
 * PÁGINA DE INVESTIMENTOS
 * 
 * Esta página mostra a lista completa de todos os investimentos do usuário.
 * É mais detalhada que o dashboard, permitindo ações específicas:
 * - Ver lista completa de investimentos
 * - Editar investimentos individuais
 * - Excluir investimentos
 * - Adicionar novos investimentos
 * - Ver resumo financeiro no topo
 * 
 * DIFERENÇA DO DASHBOARD:
 * - Dashboard: Visão geral + primeiros investimentos
 * - Investments: Lista completa + ações de gerenciamento
 */

// Incluir classes necessárias
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

// Criar instâncias das classes
$auth = new Auth();
$investment = new Investment();

// VERIFICAR AUTENTICAÇÃO
// Garantir que apenas usuários logados acessem esta página
$auth->requireLogin();

// OBTER DADOS DO USUÁRIO ATUAL
$user = $auth->getCurrentUser();

// BUSCAR TODOS OS INVESTIMENTOS DO USUÁRIO
// Diferente do dashboard que pode limitar a quantidade
$investments = $investment->getAllByUser($user['id']);

// BUSCAR RESUMO FINANCEIRO PARA EXIBIR NO TOPO
$summary = $investment->getSummaryByUser($user['id']);

// TRATAMENTO DE DADOS NULOS
// Garantir que sempre temos valores para exibir
if (!$summary['total_investido']) {
    $summary['total_investido'] = 0;
    $summary['total_atual'] = 0;
    $summary['total_rendimento'] = 0;
    $summary['total_investimentos'] = 0;
}

// CALCULAR PERCENTUAL DE RENDIMENTO GERAL
$percentual_geral = $summary['total_investido'] > 0 ? 
    ($summary['total_rendimento'] / $summary['total_investido']) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Investimentos - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="investments.php" class="active">Investimentos</a></li>
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
                    <h1>Meus Investimentos</h1>
                    <p class="text-secondary">Gerencie sua carteira completa</p>
                </div>
                <a href="add_investment.php" class="btn btn-primary">
                    + Adicionar Investimento
                </a>
            </div>

            <!-- RESUMO FINANCEIRO COMPACTO -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                        <div style="text-align: center;">
                            <h3 style="color: #ccc; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Total Investido</h3>
                            <div class="stat-value">R$ <?= number_format($summary['total_investido'] ?? 0, 2, ',', '.') ?></div>
                            <div style="font-size: 0.9rem; color: #ccc;"><?= $summary['total_investimentos'] ?> investimentos</div>
                        </div>
                        
                        <div style="text-align: center;">
                            <h3 style="color: #ccc; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Valor Atual</h3>
                            <div class="stat-value">R$ <?= number_format($summary['total_atual'] ?? 0, 2, ',', '.') ?></div>
                            <div style="font-size: 0.9rem; color: #ccc;">Patrimônio total</div>
                        </div>
                        
                        <div style="text-align: center;">
                            <h3 style="color: #ccc; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Rendimento</h3>
                            <div class="stat-value <?= ($summary['total_rendimento'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                                R$ <?= number_format($summary['total_rendimento'] ?? 0, 2, ',', '.') ?>
                            </div>
                            <div style="font-size: 0.9rem;" class="<?= $percentual_geral >= 0 ? 'positive' : 'negative' ?>">
                                <?= $percentual_geral >= 0 ? '+' : '' ?><?= number_format($percentual_geral, 2, ',', '.') ?>%
                            </div>
                        </div>
                        
                        <div style="text-align: center;">
                            <h3 style="color: #ccc; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 0.5rem;">Rentabilidade</h3>
                            <div class="stat-value <?= $percentual_geral >= 0 ? 'positive' : 'negative' ?>">
                                <?= $percentual_geral >= 0 ? '+' : '' ?><?= number_format($percentual_geral, 2, ',', '.') ?>%
                            </div>
                            <div style="font-size: 0.9rem; color: #ccc;">No período total</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEÇÃO DE INVESTIMENTOS -->
            <div class="card">
                <!-- CABEÇALHO DA SEÇÃO -->
                <div class="card-header">
                    <h2 class="card-title">📊 Lista Completa de Investimentos</h2>
                    <div style="color: #ccc; font-size: 0.9rem;">
                        <?php echo count($investments); ?> investimento(s) cadastrado(s)
                    </div>
                </div>
                
                <div class="card-body">
                <?php if (count($investments) > 0): ?>
                    <!-- TABELA COMPLETA DE INVESTIMENTOS -->
                    <div class="table-container">
                        <table class="table">
                            <!-- CABEÇALHO DA TABELA -->
                            <thead>
                                <tr>
                                    <th>Investimento</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Preço Médio</th>
                                    <th>Valor Investido</th>
                                    <th>Valor Atual</th>
                                    <th>Rendimento</th>
                                    <th>Data Compra</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <!-- CORPO DA TABELA -->
                            <tbody>
                                <?php foreach ($investments as $inv): ?>
                                    <tr>
                                        <!-- COLUNA: Nome e Ticker -->
                                        <td>
                                            <div class="text-primary-theme" style="font-weight: 600;"><?php echo htmlspecialchars($inv['nome']); ?></div>
                                            <?php if ($inv['ticker']): ?>
                                                <div style="color: #ccc; font-size: 0.9rem;"><?php echo htmlspecialchars($inv['ticker']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <!-- COLUNA: Tipo (com badge colorido) -->
                                        <td>
                                            <span class="badge" style="background-color: <?php echo htmlspecialchars($inv['tipo_cor']); ?>">
                                                <?php echo htmlspecialchars($inv['tipo_nome']); ?>
                                            </span>
                                        </td>
                                        
                                        <!-- COLUNA: Quantidade -->
                                        <td><?php echo number_format($inv['quantidade'], 2, ',', '.'); ?></td>
                                        
                                        <!-- COLUNA: Preço Médio -->
                                        <td>R$ <?php echo number_format($inv['preco_medio'], 2, ',', '.'); ?></td>
                                        
                                        <!-- COLUNA: Valor Investido -->
                                        <td>R$ <?php echo number_format($inv['valor_investido'], 2, ',', '.'); ?></td>
                                        
                                        <!-- COLUNA: Valor Atual -->
                                        <td>R$ <?php echo number_format($inv['valor_atual'], 2, ',', '.'); ?></td>
                                        
                                        <!-- COLUNA: Rendimento (com cores) -->
                                        <td class="<?php echo $inv['rendimento'] >= 0 ? 'positive' : 'negative'; ?>">
                                            R$ <?php echo number_format($inv['rendimento'], 2, ',', '.'); ?>
                                            <br>
                                            <small>
                                                <?php echo $inv['percentual_rendimento'] >= 0 ? '+' : ''; ?>
                                                <?php echo number_format($inv['percentual_rendimento'], 2, ',', '.'); ?>%
                                            </small>
                                        </td>
                                        
                                        <!-- COLUNA: Data de Compra -->
                                        <td><?php echo date('d/m/Y', strtotime($inv['data_compra'])); ?></td>
                                        
                                        <!-- COLUNA: Botões de Ação -->
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <!-- Botão Editar -->
                                                <a href="edit_investment.php?id=<?php echo $inv['id']; ?>" class="btn btn-secondary" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                                                    ✏️ Editar
                                                </a>
                                                <!-- Botão Excluir -->
                                                <a href="delete_investment.php?id=<?php echo $inv['id']; ?>" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.5rem 1rem;"
                                                   onclick="return confirm('Tem certeza que deseja excluir este investimento?')">
                                                    🗑️ Excluir
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- ESTADO VAZIO: Quando não há investimentos -->
                    <div style="text-align: center; padding: 4rem 2rem; color: #ccc;">
                        <h3>📈 Nenhum investimento cadastrado</h3>
                        <p>Você ainda não possui investimentos em sua carteira.</p>
                        <p>Comece adicionando seu primeiro investimento para começar a acompanhar sua evolução patrimonial.</p>
                        <br>
                        <a href="add_investment.php" class="btn btn-primary">Adicionar Primeiro Investimento</a>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/**
 * RESUMO DO ARQUIVO investments.php:
 * 
 * OBJETIVO:
 * Exibir lista completa e detalhada de todos os investimentos do usuário
 * com opções de gerenciamento (editar/excluir).
 * 
 * FUNCIONALIDADES:
 * 1. Verificar autenticação do usuário
 * 2. Buscar TODOS os investimentos (sem limite)
 * 3. Exibir resumo financeiro compacto no topo
 * 4. Mostrar tabela completa com TODAS as colunas
 * 5. Botões de ação para cada investimento
 * 6. Confirmação JavaScript para exclusão
 * 
 * DIFERENÇAS DO DASHBOARD:
 * - Dashboard: Visão geral + primeiros investimentos
 * - Investments: Lista completa + ações de gerenciamento
 * - Dashboard: Foco em estatísticas e gráficos
 * - Investments: Foco em gerenciamento individual
 * 
 * COLUNAS DA TABELA:
 * 1. Investimento (nome + ticker)
 * 2. Tipo (badge colorido)
 * 3. Quantidade de cotas/ações
 * 4. Preço médio pago
 * 5. Valor total investido
 * 6. Valor atual do investimento
 * 7. Rendimento (R$ e %)
 * 8. Data da compra
 * 9. Ações (editar/excluir)
 * 
 * RECURSOS ESPECIAIS:
 * - Scroll horizontal em mobile para tabela grande
 * - Confirmação JavaScript antes de excluir
 * - Cores indicativas para ganhos/perdas
 * - Estado vazio quando não há dados
 * - Contador de investimentos no cabeçalho
 * 
 * NAVEGAÇÃO:
 * - Link ativo destacado no menu
 * - Botão para adicionar novo investimento
 * - Links para editar/excluir cada item
 * 
 * SEGURANÇA:
 * - Verificação de login obrigatória
 * - Dados filtrados por usuário
 * - htmlspecialchars() para prevenir XSS
 * - Confirmação antes de ações destrutivas
 */
?> 