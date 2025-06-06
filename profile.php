<?php
require_once 'classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$message = '';
$error = '';

// Processar atualização do perfil
if($_POST && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if(empty($nome) || empty($email)) {
        $error = 'Nome e email são obrigatórios.';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            // Verificar se o email já existe para outro usuário
            $query = "SELECT id FROM usuarios WHERE email = :email AND id != :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $error = 'Este email já está sendo usado por outro usuário.';
            } else {
                // Atualizar dados do usuário
                $query = "UPDATE usuarios SET nome = :nome, email = :email WHERE id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':nome', $nome);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':user_id', $user['id']);
                
                if($stmt->execute()) {
                    // Atualizar sessão
                    $_SESSION['user_name'] = $nome;
                    $_SESSION['user_email'] = $email;
                    $user = $auth->getCurrentUser(); // Recarregar dados
                    $message = 'Perfil atualizado com sucesso!';
                } else {
                    $error = 'Erro ao atualizar perfil.';
                }
            }
        } catch(Exception $e) {
            $error = 'Erro ao atualizar perfil: ' . $e->getMessage();
        }
    }
}

// Processar mudança de senha
if($_POST && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if(empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $error = 'Todos os campos de senha são obrigatórios.';
    } elseif($nova_senha !== $confirmar_senha) {
        $error = 'A nova senha e confirmação não coincidem.';
    } elseif(strlen($nova_senha) < 6) {
        $error = 'A nova senha deve ter pelo menos 6 caracteres.';
    } else {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            
            // Verificar senha atual
            $query = "SELECT senha FROM usuarios WHERE id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if(password_verify($senha_atual, $row['senha'])) {
                    // Atualizar senha
                    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                    $query = "UPDATE usuarios SET senha = :senha WHERE id = :user_id";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':senha', $nova_senha_hash);
                    $stmt->bindParam(':user_id', $user['id']);
                    
                    if($stmt->execute()) {
                        $message = 'Senha alterada com sucesso!';
                    } else {
                        $error = 'Erro ao alterar senha.';
                    }
                } else {
                    $error = 'Senha atual incorreta.';
                }
            }
        } catch(Exception $e) {
            $error = 'Erro ao alterar senha: ' . $e->getMessage();
        }
    }
}

// Buscar estatísticas do usuário
require_once 'classes/Investment.php';
$investment = new Investment();
$summary = $investment->getSummaryByUser($user['id']);
$total_investments = count($investment->getAllByUser($user['id']));

// Data de cadastro
try {
    $database = new Database();
    $conn = $database->getConnection();
    $query = "SELECT data_criacao FROM usuarios WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user['id']);
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $data_cadastro = $userData['data_criacao'];
} catch(Exception $e) {
    $data_cadastro = null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Carteira de Investimentos</title>
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
                <li><a href="estatisticas.php">Estatísticas</a></li>
                <li><a href="profile.php" class="active">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <div class="page-header">
                <h1>Meu Perfil</h1>
                <p style="color: #ccc;">Gerencie suas informações pessoais e configurações</p>
            </div>

            <?php if($message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <!-- Informações do Usuário -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Informações Pessoais</h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-group">
                                <label for="nome" class="form-label">Nome Completo</label>
                                <input type="text" 
                                       id="nome" 
                                       name="nome" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($user['nome']) ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($user['email']) ?>"
                                       required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                Atualizar Perfil
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Estatísticas -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Estatísticas da Conta</h2>
                    </div>
                    <div class="card-body">
                        <div class="stats-list">
                            <div class="stat-item">
                                <span class="stat-label">Membro desde:</span>
                                <span class="stat-value">
                                    <?= $data_cadastro ? date('d/m/Y', strtotime($data_cadastro)) : 'N/A' ?>
                                </span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Total de investimentos:</span>
                                <span class="stat-value"><?= $total_investments ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Valor total investido:</span>
                                <span class="stat-value">R$ <?= number_format($summary['total_investido'] ?? 0, 2, ',', '.') ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Rendimento total:</span>
                                <span class="stat-value" style="color: <?= ($summary['total_rendimento'] ?? 0) >= 0 ? '#00C851' : '#FF4444' ?>">
                                    R$ <?= number_format($summary['total_rendimento'] ?? 0, 2, ',', '.') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mudança de Senha -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Alterar Senha</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" style="max-width: 400px;">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="senha_atual" class="form-label">Senha Atual</label>
                            <input type="password" 
                                   id="senha_atual" 
                                   name="senha_atual" 
                                   class="form-control" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="nova_senha" class="form-label">Nova Senha</label>
                            <input type="password" 
                                   id="nova_senha" 
                                   name="nova_senha" 
                                   class="form-control" 
                                   minlength="6"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" 
                                   id="confirmar_senha" 
                                   name="confirmar_senha" 
                                   class="form-control" 
                                   minlength="6"
                                   required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            Alterar Senha
                        </button>
                    </form>
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
    .page-header {
        margin-bottom: 2rem;
    }
    
    .page-header h1 {
        margin-bottom: 0.5rem;
    }
    
    .nav-links a.active {
        color: var(--primary-light) !important;
        border-bottom: 2px solid var(--primary-light);
    }
    
    .stats-list {
        display: flex;
        flex-direction: column;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #333;
    }

    .stat-item:not(:first-child) {
        padding-top: 1rem;
    }

    .stat-item:not(:last-child) {
        padding-bottom: 1rem;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: #ccc;
        font-weight: 500;
    }
    
    .stat-value {
        color: #fff;
        font-weight: bold;
    }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
        border: 1px solid;
    }
    
    .alert-success {
        background-color: rgba(0, 200, 81, 0.1);
        border-color: #00C851;
        color: #00C851;
    }
    
    .alert-danger {
        background-color: rgba(255, 68, 68, 0.1);
        border-color: #FF4444;
        color: #FF4444;
    }
    
    @media (max-width: 768px) {
        div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
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
        // Verificar se os elementos existem antes de adicionar listeners
        const modalQuantidade = document.getElementById('modal_quantidade');
        const modalPrecoMedio = document.getElementById('modal_preco_medio');
        const modalValorAtual = document.getElementById('modal_valor_atual');
        
        if (modalQuantidade) modalQuantidade.addEventListener('input', calcularValoresModal);
        if (modalPrecoMedio) modalPrecoMedio.addEventListener('input', calcularValoresModal);
        if (modalValorAtual) modalValorAtual.addEventListener('input', calcularValoresModal);
        
        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('addInvestmentModal') && document.getElementById('addInvestmentModal').style.display === 'flex') {
                closeAddInvestmentModal();
            }
        });
        
        // Fechar modal clicando fora
        const modal = document.getElementById('addInvestmentModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAddInvestmentModal();
                }
            });
        }
    });
    </script>
</body>
</html> 