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
    <!-- Chart.js para gráficos interativos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="metas.php">Metas</a></li>
                <li><a href="calculadora.php">Calculadora</a></li>
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
                    <h1>Dashboard</h1>
                    <p class="text-secondary">Visão geral dos seus investimentos</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button id="updatePricesBtn" class="btn btn-secondary">
                        🔄 Atualizar Cotações
                    </button>
                    <a href="add_investment.php" class="btn btn-primary">
                        + Adicionar Investimento
                    </a>
                </div>
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

            <!-- SEÇÃO DE GRÁFICOS -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Gráfico: Distribuição por Tipo -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📊 Distribuição por Tipo</h2>
                    </div>
                    <div class="card-body">
                        <canvas id="distributionChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>

                <!-- Gráfico: Performance -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📈 Performance da Carteira</h2>
                    </div>
                    <div class="card-body">
                        <canvas id="performanceChart" style="max-height: 300px;"></canvas>
                    </div>
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
                                    <th>Ações</th>
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
                                            <span class="badge">
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

                                        <!-- Coluna: Ações -->
                                        <td>
                                            <a href="edit_investment.php?id=<?php echo $inv['id']; ?>" class="btn btn-sm btn-secondary" title="Editar">
                                                ✏️
                                            </a>
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

    <!-- JavaScript para Gráficos e Funcionalidades Avançadas -->
    <script>
        // Configurações dos gráficos
        Chart.defaults.color = '#ffffff';
        Chart.defaults.font.family = 'Inter, sans-serif';

        // Dados PHP convertidos para JavaScript
        const summaryByType = <?php echo json_encode($summaryByType); ?>;
        const investments = <?php echo json_encode($investments); ?>;

        // Definir cores consistentes para cada tipo de investimento
        const chartColors = [
            '#FFD700', // Dourado
            '#FF6B6B', // Vermelho
            '#4ECDC4', // Azul água
            '#45B7D1', // Azul
            '#96CEB4', // Verde
            '#FFEAA7', // Amarelo
            '#DDA0DD', // Roxo claro
            '#98D8C8'  // Verde água
        ];

        // Criar mapeamento de cores por tipo
        const colorMap = {};
        if (summaryByType && summaryByType.length > 0) {
            summaryByType.forEach((item, index) => {
                colorMap[item.tipo] = chartColors[index % chartColors.length];
            });
        }

        // Aplicar cores aos badges
        function applyBadgeColors() {
            const badges = document.querySelectorAll('.badge');
            badges.forEach(badge => {
                const tipoNome = badge.textContent.trim();
                if (colorMap[tipoNome]) {
                    badge.style.backgroundColor = colorMap[tipoNome];
                    badge.style.color = '#000'; // Texto preto para melhor contraste
                }
            });
        }

        // Aplicar cores após carregar o DOM
        document.addEventListener('DOMContentLoaded', applyBadgeColors);

        // GRÁFICO DE DISTRIBUIÇÃO POR TIPO (Pizza)
        if (summaryByType && summaryByType.length > 0) {
            const distributionCtx = document.getElementById('distributionChart').getContext('2d');
            new Chart(distributionCtx, {
                type: 'doughnut',
                data: {
                    labels: summaryByType.map(item => item.tipo),
                    datasets: [{
                        data: summaryByType.map(item => item.valor_atual),
                        backgroundColor: summaryByType.map((item, index) => chartColors[index % chartColors.length]),
                        borderWidth: 2,
                        borderColor: '#1a1a1a'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                color: '#ffffff'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.raw / total) * 100).toFixed(1);
                                    return context.label + ': R$ ' + context.raw.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2
                                    }) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // GRÁFICO DE PERFORMANCE (Simular evolução histórica)
        if (investments && investments.length > 0) {
            const performanceCtx = document.getElementById('performanceChart').getContext('2d');
            
            // Simular dados históricos (implementar com dados reais posteriormente)
            const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'];
            const totalInvestido = <?php echo $summary['total_investido']; ?>;
            const totalAtual = <?php echo $summary['total_atual']; ?>;
            
            // Simular evolução progressiva
            const evolutionData = months.map((month, index) => {
                const progress = (index + 1) / months.length;
                return totalInvestido + ((totalAtual - totalInvestido) * progress);
            });

            new Chart(performanceCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Valor da Carteira',
                        data: evolutionData,
                        borderColor: '#2196F3',
                        backgroundColor: 'rgba(33, 150, 243, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2196F3',
                        pointBorderColor: '#1a1a1a',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Valor: R$ ' + context.raw.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#333333'
                            },
                            ticks: {
                                color: '#ffffff'
                            }
                        },
                        y: {
                            grid: {
                                color: '#333333'
                            },
                            ticks: {
                                color: '#ffffff',
                                callback: function(value) {
                                    return 'R$ ' + value.toLocaleString('pt-BR', {
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            }
                        }
                    }
                }
            });
        }

        // FUNCIONALIDADE: Atualizar Cotações
        document.getElementById('updatePricesBtn').addEventListener('click', function() {
            const btn = this;
            const originalText = btn.innerHTML;
            
            // Estado de carregamento
            btn.innerHTML = '⏳ Atualizando...';
            btn.disabled = true;
            
            // Testar API primeiro
            fetch('api/test_api.php')
            .then(response => response.json())
            .then(testData => {
                if (testData.success) {
                    // API funciona, tentar atualizar cotações
                    return fetch('api/update_prices.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        credentials: 'same-origin'
                    });
                } else {
                    throw new Error('API não está funcionando');
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Resposta da API não foi bem-sucedida');
                }
                return response.text(); // Pegar como texto primeiro
            })
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    
                    if (data.success) {
                        const { updated, errors, messages } = data.data;
                        
                        if (updated > 0) {
                            showNotification(`✅ ${updated} cotações atualizadas com sucesso!`, 'success');
                            
                            // Mostrar detalhes se houver
                            if (messages && messages.length > 0) {
                                console.log('Cotações atualizadas:', messages);
                            }
                            
                            // Recarregar página após sucesso
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            showNotification('⚠️ Nenhuma cotação foi atualizada', 'warning');
                        }
                        
                    } else {
                        throw new Error(data.error || 'Erro desconhecido');
                    }
                } catch (parseError) {
                    console.error('Erro ao interpretar resposta:', text);
                    showNotification('❌ Erro na resposta da API', 'error');
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar cotações:', error);
                
                // Fallback: simular atualização
                showNotification('⚠️ Modo offline - simulando atualização', 'warning');
                
                setTimeout(() => {
                    showNotification('✅ Funcionalidade de cotações será implementada', 'info');
                }, 1000);
            })
            .finally(() => {
                // Restaurar botão
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });

        // SISTEMA DE NOTIFICAÇÕES
        function showNotification(message, type = 'info') {
            // Criar elemento de notificação
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            
            let backgroundColor;
            switch(type) {
                case 'success': backgroundColor = '#00C851'; break;
                case 'warning': backgroundColor = '#FF8800'; break;
                case 'error': backgroundColor = '#FF4444'; break;
                default: backgroundColor = '#2196F3';
            }
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${backgroundColor};
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                z-index: 10000;
                animation: slideIn 0.5s ease;
                max-width: 400px;
                font-weight: 500;
                line-height: 1.4;
            `;
            notification.textContent = message;
            
            // Adicionar CSS de animação se não existir
            if (!document.getElementById('notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOut {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                `;
                document.head.appendChild(style);
            }
            
            // Adicionar ao DOM
            document.body.appendChild(notification);
            
            // Remover após 3 segundos
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.5s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 500);
            }, 3000);
        }

        // ANIMAÇÕES DE ENTRADA
        document.addEventListener('DOMContentLoaded', function() {
            // Animar cards de estatísticas
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.animation = 'fadeInUp 0.6s ease forwards';
                }, index * 100);
            });

            // CSS para animações
            if (!document.getElementById('animation-styles')) {
                const style = document.createElement('style');
                style.id = 'animation-styles';
                style.textContent = `
                    @keyframes fadeInUp {
                        from {
                            opacity: 0;
                            transform: translateY(30px);
                        }
                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                    .stat-card {
                        opacity: 0;
                    }
                `;
                document.head.appendChild(style);
            }
        });

        // AUTO-REFRESH DOS DADOS (opcional, a cada 5 minutos)
        let autoRefreshInterval;
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(() => {
                console.log('Auto-refresh executado');
                // Implementar atualização silenciosa dos dados
            }, 300000); // 5 minutos
        }

        // Iniciar auto-refresh se houver investimentos
        if (investments && investments.length > 0) {
            startAutoRefresh();
        }

        // Limpar interval ao sair da página
        window.addEventListener('beforeunload', () => {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        });
    </script>
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