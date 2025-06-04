<?php
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$investment = new Investment();

// Verificar se foi fornecido um ID
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: investments.php?error=ID inválido');
    exit();
}

$investment_id = (int)$_GET['id'];

// Verificar se o investimento pertence ao usuário
$investmentData = $investment->getById($investment_id, $user['id']);
if(!$investmentData) {
    header('Location: investments.php?error=Investimento não encontrado');
    exit();
}

// Processar exclusão
if($_POST && isset($_POST['confirm_delete'])) {
    try {
        if($investment->delete($investment_id, $user['id'])) {
            header('Location: investments.php?success=Investimento excluído com sucesso');
            exit();
        } else {
            $error = 'Erro ao excluir investimento.';
        }
    } catch(Exception $e) {
        $error = 'Erro ao excluir investimento: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Investimento - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/stock-ticker.js"></script>
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="add_investment.php">Adicionar</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="fade-in">
            <div class="page-header">
                <h1>Excluir Investimento</h1>
                <p style="color: #ccc;">Confirme a exclusão do investimento</p>
            </div>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <div class="card-header">
                    <h2 class="card-title" style="color: #FF4444;">⚠️ Confirmar Exclusão</h2>
                </div>
                <div class="card-body">
                    <div class="warning-box">
                        <p><strong>Você está prestes a excluir o seguinte investimento:</strong></p>
                        
                        <div class="investment-details">
                            <div class="detail-row">
                                <span class="label">Nome:</span>
                                <span class="value"><?= htmlspecialchars($investmentData['nome']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Tipo:</span>
                                <span class="value"><?= htmlspecialchars($investmentData['tipo_nome']) ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Ticker:</span>
                                <span class="value"><?= htmlspecialchars($investmentData['ticker'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Valor Investido:</span>
                                <span class="value">R$ <?= number_format($investmentData['valor_investido'], 2, ',', '.') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Valor Atual:</span>
                                <span class="value">R$ <?= number_format($investmentData['valor_atual'], 2, ',', '.') ?></span>
                            </div>
                        </div>
                        
                        <div class="warning-text">
                            <p style="color: #FF4444; font-weight: bold; margin: 1.5rem 0;">
                                ⚠️ Esta ação não pode ser desfeita!
                            </p>
                            <p style="color: #ccc;">
                                Todos os dados relacionados a este investimento, incluindo histórico de transações, 
                                serão permanentemente removidos do sistema.
                            </p>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <form method="POST" action="" style="display: inline;">
                            <input type="hidden" name="confirm_delete" value="1">
                            <button type="submit" class="btn btn-danger">
                                🗑️ Sim, Excluir Investimento
                            </button>
                        </form>
                        
                        <a href="investments.php" class="btn btn-secondary">
                            ← Cancelar e Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .page-header {
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .page-header h1 {
        margin-bottom: 0.5rem;
    }
    
    .warning-box {
        background-color: rgba(255, 68, 68, 0.05);
        border: 1px solid #FF4444;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .investment-details {
        background-color: rgba(0, 0, 0, 0.3);
        border-radius: 6px;
        padding: 1rem;
        margin: 1rem 0;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #333;
    }
    
    .detail-row:last-child {
        border-bottom: none;
    }
    
    .detail-row .label {
        color: #ccc;
        font-weight: 500;
    }
    
    .detail-row .value {
        color: #fff;
        font-weight: bold;
    }
    
    .warning-text {
        text-align: center;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .btn-danger {
        background-color: #FF4444;
        border-color: #FF4444;
    }
    
    .btn-danger:hover {
        background-color: #cc3333;
        border-color: #cc3333;
    }
    
    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 8px;
        border: 1px solid;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .alert-danger {
        background-color: rgba(255, 68, 68, 0.1);
        border-color: #FF4444;
        color: #FF4444;
    }
    
    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .detail-row {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
    </style>
</body>
</html> 