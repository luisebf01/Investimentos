<?php
session_start();

// Verificar se usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'classes/Auth.php';
require_once 'classes/AuditLog.php';

$auth = new Auth();
$auditLog = new AuditLog();

// Verificar se usuário está logado
$auth->requireLogin();

// Obter dados do usuário
$user_data = $auth->getCurrentUser();
$user_id = $user_data['id'];

// Parâmetros de paginação e filtros
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$registros_por_pagina = 25;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

$filtro_acao = $_GET['acao'] ?? '';
$filtro_tabela = $_GET['tabela'] ?? '';
$periodo_dias = isset($_GET['periodo']) ? intval($_GET['periodo']) : 30;

// Buscar histórico
$historico = $auditLog->getHistoricoUsuario($user_id, $registros_por_pagina, $offset, $filtro_acao, $filtro_tabela);
$total_registros = $auditLog->contarLogsUsuario($user_id, $filtro_acao, $filtro_tabela);
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Buscar operações financeiras recentes
$operacoes_financeiras = $auditLog->getOperacoesFinanceiras($user_id, 10);

// Buscar estatísticas de atividade
$estatisticas = $auditLog->getEstatisticasAtividade($user_id, $periodo_dias);

// Processar estatísticas para resumo
$stats_por_acao = [];
foreach ($estatisticas as $stat) {
    if (!isset($stats_por_acao[$stat['acao']])) {
        $stats_por_acao[$stat['acao']] = 0;
    }
    $stats_por_acao[$stat['acao']] += $stat['quantidade'];
}

// Calcular estatísticas de resumo
$total_acoes = array_sum($stats_por_acao);
$acao_mais_comum = $total_acoes > 0 ? array_key_first(array_reverse($stats_por_acao, true)) : 'Nenhuma';
$atividade_recente = count($historico);
$operacoes_financeiras_count = count($operacoes_financeiras);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Atividades - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Estilos específicos para o histórico */
        .historico-filtros {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 15px;
            box-shadow: 0 5px 15px var(--shadow-light);
        }
        
        .historico-filtros .form-control {
            background: var(--bg-input);
            border: 1px solid var(--border-light);
            color: var(--text-primary);
            transition: all 0.3s ease;
        }
        
        .historico-filtros .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px var(--primary-focus);
        }
        
        .tab-button {
            transition: all 0.3s ease;
            border-radius: 8px 8px 0 0;
            background: rgba(var(--bg-secondary), 0.5);
        }
        
        .tab-button:hover {
            color: var(--primary-light) !important;
            background: rgba(66, 165, 245, 0.1);
        }
        
        .historico-table-wrapper {
            background: var(--bg-card);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px var(--shadow-light);
        }
        
        .badge {
            border: 1px solid currentColor;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .paginacao-wrapper {
            background: var(--bg-card);
            border-radius: 10px;
            padding: 1rem;
            border: 1px solid var(--border-color);
        }
        
        .valor-variacao {
            font-weight: 600;
            font-family: 'Courier New', monospace;
        }
        
        @media (max-width: 768px) {
            .historico-filtros form {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .historico-filtros .form-group {
                margin-bottom: 1rem;
            }
            
            .table-container {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 0.75rem 0.5rem;
            }
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
                <li><a href="calculadora.php">Calculadora</a></li>
                <li><a href="relatorios.php">Relatórios</a></li>
                <li><a href="historico.php" class="active">Histórico</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <!-- CABEÇALHO DA PÁGINA -->
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>📋 Histórico de Atividades</h1>
                    <p class="text-secondary">Acompanhe todas as transações e operações realizadas</p>
                </div>
            </div>

            <!-- CARDS DE ESTATÍSTICAS -->
            <div class="stats-row">
                <div class="stat-card">
                    <h3>Total de Ações</h3>
                    <div class="stat-value neutral"><?php echo number_format($total_acoes, 0, ',', '.'); ?></div>
                    <div class="stat-label">Nos últimos <?php echo $periodo_dias; ?> dias</div>
                </div>
                
                <div class="stat-card">
                    <h3>Atividade Recente</h3>
                    <div class="stat-value neutral"><?php echo $atividade_recente; ?></div>
                    <div class="stat-label">Registros na página atual</div>
                </div>
                
                <div class="stat-card">
                    <h3>Operações Financeiras</h3>
                    <div class="stat-value neutral"><?php echo $operacoes_financeiras_count; ?></div>
                    <div class="stat-label">Transações recentes</div>
                </div>
                
                <div class="stat-card">
                    <h3>Ação Mais Comum</h3>
                    <div class="stat-value neutral" style="font-size: 1.2rem;"><?php echo ucfirst(str_replace('_', ' ', $acao_mais_comum)); ?></div>
                    <div class="stat-label">No período selecionado</div>
                </div>
            </div>

            <!-- FILTROS -->
            <div class="card historico-filtros">
                <div class="card-header">
                    <h2 class="card-title">🔍 Filtros</h2>
                </div>
                <div class="card-body">
                    <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Tipo de Ação:</label>
                            <select name="acao" class="form-control form-select">
                                <option value="">Todas as ações</option>
                                <option value="create" <?php echo $filtro_acao === 'create' ? 'selected' : ''; ?>>Criação</option>
                                <option value="update" <?php echo $filtro_acao === 'update' ? 'selected' : ''; ?>>Atualização</option>
                                <option value="delete" <?php echo $filtro_acao === 'delete' ? 'selected' : ''; ?>>Exclusão</option>
                                <option value="login" <?php echo $filtro_acao === 'login' ? 'selected' : ''; ?>>Login</option>
                                <option value="logout" <?php echo $filtro_acao === 'logout' ? 'selected' : ''; ?>>Logout</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Tabela Afetada:</label>
                            <select name="tabela" class="form-control form-select">
                                <option value="">Todas as tabelas</option>
                                <option value="investimentos" <?php echo $filtro_tabela === 'investimentos' ? 'selected' : ''; ?>>Investimentos</option>
                                <option value="metas_investimento" <?php echo $filtro_tabela === 'metas_investimento' ? 'selected' : ''; ?>>Metas</option>
                                <option value="usuarios" <?php echo $filtro_tabela === 'usuarios' ? 'selected' : ''; ?>>Usuários</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label">Período:</label>
                            <select name="periodo" class="form-control form-select">
                                <option value="7" <?php echo $periodo_dias === 7 ? 'selected' : ''; ?>>7 dias</option>
                                <option value="30" <?php echo $periodo_dias === 30 ? 'selected' : ''; ?>>30 dias</option>
                                <option value="90" <?php echo $periodo_dias === 90 ? 'selected' : ''; ?>>90 dias</option>
                                <option value="365" <?php echo $periodo_dias === 365 ? 'selected' : ''; ?>>1 ano</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                            
                            <?php if ($filtro_acao || $filtro_tabela): ?>
                                <a href="historico.php" class="btn btn-secondary">🗑️ Limpar</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ABAS DE CONTEÚDO -->
            <div style="display: flex; gap: 1rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                <button class="tab-button active" onclick="showTab('historico-geral')" style="background: none; border: none; color: var(--primary-light); padding: 0.5rem 1rem; cursor: pointer; border-bottom: 2px solid var(--primary-light);">
                    📋 Histórico Geral
                </button>
                <button class="tab-button" onclick="showTab('operacoes-financeiras')" style="background: none; border: none; color: var(--text-secondary); padding: 0.5rem 1rem; cursor: pointer; border-bottom: 2px solid transparent;">
                    💰 Operações Financeiras
                </button>
            </div>

            <!-- CONTEÚDO DAS ABAS -->
            
            <!-- ABA: HISTÓRICO GERAL -->
            <div id="historico-geral" class="tab-content active">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📋 Histórico Geral de Atividades</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($historico) > 0): ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Data/Hora</th>
                                            <th>Ação</th>
                                            <th>Tabela</th>
                                            <th>Detalhes</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($historico as $log): ?>
                                            <tr>
                                                <td>
                                                    <div style="font-weight: 600;"><?php echo date('d/m/Y', strtotime($log['data_acao'])); ?></div>
                                                    <div style="color: var(--text-secondary); font-size: 0.9rem;"><?php echo date('H:i:s', strtotime($log['data_acao'])); ?></div>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $badge_class = match($log['acao']) {
                                                        'create' => 'badge badge-success',
                                                        'update' => 'badge',
                                                        'delete' => 'badge badge-danger',
                                                        'login' => 'badge',
                                                        'logout' => 'badge',
                                                        default => 'badge'
                                                    };
                                                    $action_text = match($log['acao']) {
                                                        'create' => '✅ Criação',
                                                        'update' => '📝 Atualização',
                                                        'delete' => '🗑️ Exclusão',
                                                        'login' => '🔐 Login',
                                                        'logout' => '🚪 Logout',
                                                        default => ucfirst($log['acao'])
                                                    };
                                                    ?>
                                                    <span class="<?php echo $badge_class; ?>"><?php echo $action_text; ?></span>
                                                </td>
                                                <td>
                                                    <span style="color: var(--primary-light);"><?php echo ucfirst(str_replace('_', ' ', $log['tabela_afetada'])); ?></span>
                                                </td>
                                                <td>
                                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                                        <?php echo htmlspecialchars($log['detalhes'] ?: 'Nenhum detalhe disponível'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span style="font-family: monospace; color: var(--text-secondary);"><?php echo $log['ip_address']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- PAGINAÇÃO -->
                            <?php if ($total_paginas > 1): ?>
                                <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                                    <?php if ($pagina_atual > 1): ?>
                                        <a href="?pagina=<?php echo $pagina_atual - 1; ?>&acao=<?php echo $filtro_acao; ?>&tabela=<?php echo $filtro_tabela; ?>&periodo=<?php echo $periodo_dias; ?>" 
                                           class="btn btn-secondary">« Anterior</a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $pagina_atual - 2); $i <= min($total_paginas, $pagina_atual + 2); $i++): ?>
                                        <a href="?pagina=<?php echo $i; ?>&acao=<?php echo $filtro_acao; ?>&tabela=<?php echo $filtro_tabela; ?>&periodo=<?php echo $periodo_dias; ?>" 
                                           class="btn <?php echo $i === $pagina_atual ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($pagina_atual < $total_paginas): ?>
                                        <a href="?pagina=<?php echo $pagina_atual + 1; ?>&acao=<?php echo $filtro_acao; ?>&tabela=<?php echo $filtro_tabela; ?>&periodo=<?php echo $periodo_dias; ?>" 
                                           class="btn btn-secondary">Próximo »</a>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-center" style="margin-top: 1rem; color: var(--text-secondary);">
                                    Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?> (<?php echo $total_registros; ?> registros total)
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-center" style="padding: 3rem; color: var(--text-secondary);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                                <h3>Nenhum registro encontrado</h3>
                                <p>Não há atividades registradas com os filtros selecionados.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ABA: OPERAÇÕES FINANCEIRAS -->
            <div id="operacoes-financeiras" class="tab-content" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">💰 Operações Financeiras Recentes</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($operacoes_financeiras) > 0): ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Data/Hora</th>
                                            <th>Tipo de Operação</th>
                                            <th>Registro</th>
                                            <th>Valor Anterior</th>
                                            <th>Valor Novo</th>
                                            <th>Variação</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($operacoes_financeiras as $op): ?>
                                            <tr>
                                                <td>
                                                    <div style="font-weight: 600;"><?php echo date('d/m/Y', strtotime($op['data_operacao'])); ?></div>
                                                    <div style="color: var(--text-secondary); font-size: 0.9rem;"><?php echo date('H:i:s', strtotime($op['data_operacao'])); ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge"><?php echo ucfirst(str_replace('_', ' ', $op['tipo_operacao'])); ?></span>
                                                </td>
                                                <td>
                                                    <span style="color: var(--primary-light);"><?php echo htmlspecialchars($op['nome_registro']); ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($op['valor_anterior'] !== null): ?>
                                                        <span>R$ <?php echo number_format($op['valor_anterior'], 2, ',', '.'); ?></span>
                                                    <?php else: ?>
                                                        <span style="color: var(--text-secondary);">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($op['valor_novo'] !== null): ?>
                                                        <span>R$ <?php echo number_format($op['valor_novo'], 2, ',', '.'); ?></span>
                                                    <?php else: ?>
                                                        <span style="color: var(--text-secondary);">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if ($op['valor_anterior'] !== null && $op['valor_novo'] !== null) {
                                                        $variacao = $op['valor_novo'] - $op['valor_anterior'];
                                                        $cor = $variacao >= 0 ? 'var(--success-color)' : 'var(--danger-color)';
                                                        $sinal = $variacao >= 0 ? '+' : '';
                                                        $icone = $variacao >= 0 ? '📈' : '📉';
                                                        echo "<span class='valor-variacao' style='color: {$cor};'>{$icone} {$sinal}R$ " . number_format($variacao, 2, ',', '.') . "</span>";
                                                    } else {
                                                        echo "<span style='color: var(--text-secondary);'>-</span>";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center" style="padding: 3rem; color: var(--text-secondary);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
                                <h3>Nenhuma operação financeira encontrada</h3>
                                <p>Não há operações financeiras registradas recentemente.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabId) {
            // Esconder todas as abas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remover classe active de todos os botões
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.style.color = 'var(--text-secondary)';
                btn.style.borderBottomColor = 'transparent';
                btn.classList.remove('active');
            });
            
            // Mostrar aba selecionada
            document.getElementById(tabId).style.display = 'block';
            
            // Ativar botão correspondente
            event.target.style.color = 'var(--primary-light)';
            event.target.style.borderBottomColor = 'var(--primary-light)';
            event.target.classList.add('active');
        }
    </script>
</body>
</html>