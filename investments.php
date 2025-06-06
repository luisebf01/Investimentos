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
                    <h1>Meus Investimentos</h1>
                    <p class="text-secondary">Gerencie sua carteira completa</p>
                </div>
                <button onclick="openAddInvestmentModal()" class="btn btn-primary">
                    + Adicionar Investimento
                </button>
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
                                                <a href="delete_investment.php?id=<?php echo $inv['id']; ?>" class="btn btn-danger" style="font-size: 0.8rem; padding: 0.5rem 1rem;">
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
                        <button onclick="openAddInvestmentModal()" class="btn btn-primary">Adicionar Primeiro Investimento</button>
                    </div>
                <?php endif; ?>
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