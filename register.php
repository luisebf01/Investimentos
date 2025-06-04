<?php
require_once 'classes/Auth.php';

$auth = new Auth();
$error = '';
$success = '';

// Redirecionar se já estiver logado
if($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Processar cadastro
if($_POST) {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if(empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif($senha !== $confirmar_senha) {
        $error = 'As senhas não coincidem.';
    } elseif(strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } else {
        if($auth->register($nome, $email, $senha)) {
            $success = 'Conta criada com sucesso! Você pode fazer login agora.';
        } else {
            $error = 'Este email já está sendo usado por outra conta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card fade-in">
            <h1 class="login-title">💰 Criar Conta</h1>
            
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" 
                           id="nome" 
                           name="nome" 
                           class="form-control" 
                           placeholder="Seu nome completo"
                           value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="seu@email.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" 
                           id="senha" 
                           name="senha" 
                           class="form-control" 
                           placeholder="Mínimo 6 caracteres"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                    <input type="password" 
                           id="confirmar_senha" 
                           name="confirmar_senha" 
                           class="form-control" 
                           placeholder="Digite a senha novamente"
                           required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                    Criar Conta
                </button>
                
                <!-- LINK PARA LOGIN -->
                <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #333;">
                    <p class="text-secondary">Já tem conta? <a href="login.php" class="text-primary-theme" style="text-decoration: none;">Faça login aqui</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 