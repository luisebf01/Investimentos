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
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="add_investment.php">Adicionar</a></li>
                <li><a href="faturamento.php">Faturamento</a></li>
                <li><a href="balanco.php">Balanço</a></li>
                <li><a href="estatisticas.php">Estatísticas</a></li>
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
                <button onclick="openAddInvestmentModal()" class="btn btn-primary">
                    + Adicionar Investimento
                </button>
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
                            <button onclick="openAddInvestmentModal()" class="btn btn-primary">Adicionar Primeiro Investimento</button>
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

    <!-- MODAL ADICIONAR INVESTIMENTO -->
    <div id="addInvestmentModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>💰 Adicionar Novo Investimento</h2>
                <button type="button" class="modal-close" onclick="closeAddInvestmentModal()">&times;</button>
            </div>
            
            <div class="modal-body">
                <div id="modalAlert" style="display: none;"></div>
                
                <form id="addInvestmentForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <div class="form-group">
                                <label for="modal_tipo_id" class="form-label">Tipo de Investimento *</label>
                                <select id="modal_tipo_id" name="tipo_id" class="form-control" required>
                                    <option value="">Selecione o tipo</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="modal_nome" class="form-label">Nome do Investimento *</label>
                                <input type="text" 
                                       id="modal_nome" 
                                       name="nome" 
                                       class="form-control" 
                                       placeholder="Ex: Banco do Brasil, Tesouro Selic, Bitcoin"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="modal_ticker" class="form-label">Ticker/Código</label>
                                <input type="text" 
                                       id="modal_ticker" 
                                       name="ticker" 
                                       class="form-control" 
                                       placeholder="Ex: BBAS3, SELIC, BTC">
                            </div>
                            
                            <div class="form-group">
                                <label for="modal_data_compra" class="form-label">Data da Compra</label>
                                <input type="date" 
                                       id="modal_data_compra" 
                                       name="data_compra" 
                                       class="form-control">
                            </div>
                        </div>
                        
                        <div>
                            <div class="form-group">
                                <label for="modal_quantidade" class="form-label">Quantidade *</label>
                                <input type="number" 
                                       id="modal_quantidade" 
                                       name="quantidade" 
                                       class="form-control" 
                                       step="0.000001"
                                       min="0"
                                       placeholder="0.000000"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="modal_preco_medio" class="form-label">Preço Médio (R$) *</label>
                                <input type="number" 
                                       id="modal_preco_medio" 
                                       name="preco_medio" 
                                       class="form-control" 
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="modal_valor_atual" class="form-label">Valor Atual (R$)</label>
                                <input type="number" 
                                       id="modal_valor_atual" 
                                       name="valor_atual" 
                                       class="form-control" 
                                       step="0.01"
                                       min="0"
                                       placeholder="Deixe vazio para calcular automaticamente">
                                <small style="color: #888;">Se não informado, será calculado como quantidade × preço médio</small>
                            </div>
                            
                            <div id="modal_valor_calculado" style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px; margin-top: 1rem;">
                                <div><strong>Valor Investido:</strong> <span id="modal_valor_investido">R$ 0,00</span></div>
                                <div><strong>Valor Atual:</strong> <span id="modal_valor_atual_calc">R$ 0,00</span></div>
                                <div><strong>Rendimento:</strong> <span id="modal_rendimento">R$ 0,00 (0%)</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal_observacoes" class="form-label">Observações</label>
                        <textarea id="modal_observacoes" 
                                  name="observacoes" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Observações sobre este investimento..."></textarea>
                    </div>
                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" onclick="submitAddInvestmentForm()" class="btn btn-primary" id="submitBtn">
                    <span id="submitText">Adicionar Investimento</span>
                    <span id="submitLoader" style="display: none;">🔄 Processando...</span>
                </button>
                <button type="button" onclick="closeAddInvestmentModal()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <style>
    /* Estilos do Modal */
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(5px);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        animation: fadeIn 0.3s ease-out;
    }
    
    .modal-content {
        background: var(--bg-primary);
        border-radius: 12px;
        max-width: 900px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease-out;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem 2rem 1rem 2rem;
        border-bottom: 1px solid #333;
    }
    
    .modal-header h2 {
        margin: 0;
        color: var(--primary-light);
    }
    
    .modal-close {
        background: none;
        border: none;
        color: #ccc;
        font-size: 2rem;
        cursor: pointer;
        padding: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .modal-close:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    
    .modal-body {
        padding: 2rem;
    }
    
    .modal-footer {
        display: flex;
        gap: 1rem;
        padding: 1rem 2rem 2rem 2rem;
        border-top: 1px solid #333;
        justify-content: flex-end;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideIn {
        from { 
            opacity: 0;
            transform: translateY(-50px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Alert no modal */
    .modal-alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
        border: 1px solid;
    }
    
    .modal-alert-success {
        background-color: rgba(0, 200, 81, 0.1);
        border-color: #00C851;
        color: #00C851;
    }
    
    .modal-alert-danger {
        background-color: rgba(255, 68, 68, 0.1);
        border-color: #FF4444;
        color: #FF4444;
    }
    
    /* Responsividade do modal */
    @media (max-width: 768px) {
        .modal {
            padding: 1rem;
        }
        
        .modal-content {
            max-height: 95vh;
        }
        
        .modal-body > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
        
        .modal-header, .modal-body, .modal-footer {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    </style>

    <script>
    // Variáveis globais do modal
    let investmentTypes = [];
    
    // Abrir modal
    function openAddInvestmentModal() {
        document.getElementById('addInvestmentModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Carregar tipos de investimento
        loadInvestmentTypes();
        
        // Definir data padrão como hoje
        document.getElementById('modal_data_compra').value = new Date().toISOString().split('T')[0];
        
        // Focar no primeiro campo
        setTimeout(() => {
            document.getElementById('modal_tipo_id').focus();
        }, 300);
    }
    
    // Fechar modal
    function closeAddInvestmentModal() {
        document.getElementById('addInvestmentModal').style.display = 'none';
        document.body.style.overflow = 'auto';
        
        // Limpar formulário
        document.getElementById('addInvestmentForm').reset();
        hideModalAlert();
        calcularValoresModal();
    }
    
    // Carregar tipos de investimento
    async function loadInvestmentTypes() {
        try {
            const response = await fetch('get_investment_types.php');
            const data = await response.json();
            
            if (data.success) {
                investmentTypes = data.types;
                const select = document.getElementById('modal_tipo_id');
                
                // Limpar opções existentes (manter apenas a primeira)
                select.innerHTML = '<option value="">Selecione o tipo</option>';
                
                // Adicionar tipos
                data.types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.nome;
                    select.appendChild(option);
                });
            } else {
                showModalAlert('Erro ao carregar tipos de investimento', 'danger');
            }
        } catch (error) {
            showModalAlert('Erro de conexão ao carregar tipos', 'danger');
        }
    }
    
    // Enviar formulário
    async function submitAddInvestmentForm() {
        const form = document.getElementById('addInvestmentForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');
        
        // Validar formulário
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Mostrar loading
        submitText.style.display = 'none';
        submitLoader.style.display = 'inline';
        submitBtn.disabled = true;
        
        try {
            const formData = new FormData(form);
            const response = await fetch('process_add_investment.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                showModalAlert(data.message, 'success');
                
                // Aguardar um pouco e fechar modal
                setTimeout(() => {
                    closeAddInvestmentModal();
                    // Recarregar a página para mostrar o novo investimento
                    window.location.reload();
                }, 1500);
            } else {
                if (data.errors && Array.isArray(data.errors)) {
                    showModalAlert(data.errors.join('<br>'), 'danger');
                } else {
                    showModalAlert(data.message, 'danger');
                }
            }
        } catch (error) {
            showModalAlert('Erro de conexão. Tente novamente.', 'danger');
        } finally {
            // Esconder loading
            submitText.style.display = 'inline';
            submitLoader.style.display = 'none';
            submitBtn.disabled = false;
        }
    }
    
    // Mostrar alerta no modal
    function showModalAlert(message, type) {
        const alertDiv = document.getElementById('modalAlert');
        alertDiv.innerHTML = message;
        alertDiv.className = `modal-alert modal-alert-${type}`;
        alertDiv.style.display = 'block';
    }
    
    // Esconder alerta no modal
    function hideModalAlert() {
        document.getElementById('modalAlert').style.display = 'none';
    }
    
    // Calcular valores em tempo real no modal
    function calcularValoresModal() {
        const quantidade = parseFloat(document.getElementById('modal_quantidade').value) || 0;
        const precoMedio = parseFloat(document.getElementById('modal_preco_medio').value) || 0;
        const valorAtualInput = parseFloat(document.getElementById('modal_valor_atual').value) || 0;
        
        const valorInvestido = quantidade * precoMedio;
        const valorAtual = valorAtualInput > 0 ? valorAtualInput : valorInvestido;
        const rendimento = valorAtual - valorInvestido;
        const percentual = valorInvestido > 0 ? (rendimento / valorInvestido) * 100 : 0;
        
        document.getElementById('modal_valor_investido').textContent = 
            'R$ ' + valorInvestido.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        document.getElementById('modal_valor_atual_calc').textContent = 
            'R$ ' + valorAtual.toLocaleString('pt-BR', {minimumFractionDigits: 2});
        document.getElementById('modal_rendimento').textContent = 
            'R$ ' + rendimento.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + 
            ' (' + percentual.toFixed(2) + '%)';
        document.getElementById('modal_rendimento').style.color = rendimento >= 0 ? '#00C851' : '#FF4444';
    }
    
    // Event listeners para cálculos em tempo real
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modal_quantidade').addEventListener('input', calcularValoresModal);
        document.getElementById('modal_preco_medio').addEventListener('input', calcularValoresModal);
        document.getElementById('modal_valor_atual').addEventListener('input', calcularValoresModal);
        
        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('addInvestmentModal').style.display === 'flex') {
                closeAddInvestmentModal();
            }
        });
        
        // Fechar modal clicando fora
        document.getElementById('addInvestmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddInvestmentModal();
            }
        });
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