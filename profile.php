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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
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
        gap: 1rem;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #333;
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
    </style>
</body>
</html> 