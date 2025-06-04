<?php
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';
require_once 'classes/AuditLog.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$investment = new Investment();
$auditLog = new AuditLog();

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
    $senha = $_POST['password'] ?? '';
    
    if(empty($senha)) {
        $error = 'Por favor, digite sua senha para confirmar a exclusão.';
    } elseif(!$auth->verifyCurrentUserPassword($senha)) {
        $error = 'Senha incorreta. A exclusão não foi realizada.';
    } else {
        try {
            if($investment->delete($investment_id, $user['id'])) {
                // Registrar no log de auditoria
                $auditLog->logInvestimento($user['id'], 'delete', $investment_id, $investmentData, null);
                
                header('Location: investments.php?success=Investimento excluído com sucesso');
                exit();
            } else {
                $error = 'Erro ao excluir investimento.';
            }
        } catch(Exception $e) {
            $error = 'Erro ao excluir investimento: ' . $e->getMessage();
        }
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
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="investments.php" class="active">Investimentos</a></li>
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
            <div class="page-header">
                <h1>🗑️ Excluir Investimento</h1>
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
                        <button type="button" class="btn btn-danger" onclick="openDeleteModal()">
                            🗑️ Sim, Excluir Investimento
                        </button>
                        <a href="investments.php" class="btn btn-secondary">
                            ← Cancelar e Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação com Senha -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔒 Confirmação de Segurança</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p style="color: #FF4444; margin-bottom: 1rem;">
                    Para confirmar a exclusão deste investimento, digite sua senha:
                </p>
                <form method="POST" action="" id="deleteForm">
                    <input type="hidden" name="confirm_delete" value="1">
                    <div class="password-input">
                        <input type="password" name="password" id="modalPassword" 
                               placeholder="Digite sua senha" required
                               style="width: 100%; padding: 0.75rem; background: #333; border: 1px solid #555; border-radius: 6px; color: #fff; margin-bottom: 1rem;">
                    </div>
                    <div class="modal-actions">
                        <button type="submit" class="btn btn-danger">
                            🗑️ Confirmar Exclusão
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
            document.getElementById('modalPassword').focus();
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.getElementById('modalPassword').value = '';
        }

        // Fechar modal ao clicar fora dele
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Fechar modal com tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>

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
        justify-content: space-between;
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
    
    /* Estilos do Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(2px);
    }

    .modal-content {
        background-color: #1a1a1a;
        margin: 15% auto;
        border: 1px solid #333;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #333;
    }

    .modal-header h3 {
        margin: 0;
        color: #FF4444;
        font-size: 1.2rem;
    }

    .close {
        color: #999;
        font-size: 2rem;
        font-weight: bold;
        cursor: pointer;
        line-height: 1;
        transition: color 0.3s;
    }

    .close:hover {
        color: #FF4444;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-actions {
        display: flex;
        gap: 1rem;
        justify-content: space-between;
        margin-top: 1.5rem;
    }

    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .detail-row {
            flex-direction: column;
            gap: 0.25rem;
        }

        .modal-content {
            margin: 20% auto;
            width: 95%;
        }

        .modal-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html> 