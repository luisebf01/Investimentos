<?php
/**
 * PÁGINA DE LOGIN
 * 
 * Esta página permite que os usuários façam login no sistema.
 * Também processa o formulário de login quando enviado.
 * 
 * FLUXO:
 * 1. Usuário acessa a página
 * 2. Se já estiver logado, redireciona para dashboard
 * 3. Se enviou formulário, tenta fazer login
 * 4. Se login OK, vai para dashboard
 * 5. Se login falhou, mostra erro
 */

// Incluir a classe de autenticação
require_once 'classes/Auth.php';
require_once 'classes/AuditLog.php';

// Criar uma instância da classe Auth para gerenciar login
$auth = new Auth();
$auditLog = new AuditLog();

// Inicializar variável para mensagens de erro
$error = '';

// VERIFICAR SE USUÁRIO JÁ ESTÁ LOGADO
// Se já está logado, não precisa ver a página de login novamente
if($auth->isLoggedIn()) {
    // Redirecionar para dashboard (página principal após login)
    header('Location: dashboard.php');
    exit(); // Parar execução do resto da página
}

// PROCESSAR FORMULÁRIO DE LOGIN
// Verificar se o formulário foi enviado (método POST)
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegar dados enviados pelo formulário
    // trim() remove espaços em branco no início e fim
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    
    // VALIDAÇÕES BÁSICAS
    // Verificar se campos não estão vazios
    if(empty($email) || empty($senha)) {
        $error = 'Por favor, preencha todos os campos.';
    } 
    // Verificar se email tem formato válido
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } 
    else {
        // TENTAR FAZER LOGIN
        // Chamar método login() da classe Auth
        if($auth->login($email, $senha)) {
            // LOGIN SUCESSO! Registrar no log de auditoria
            session_start();
            $user_id = $_SESSION['user_id'];
            $session_id = session_id();
            $auditLog->registrarLogin($user_id, $session_id);
            
            // Redirecionar para dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            // LOGIN FALHOU - Email ou senha incorretos
            $error = 'Email ou senha incorretos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <!-- CABEÇALHO DA PÁGINA -->
        <div class="login-card">
            <h1 class="login-title">💰 Carteira de Investimentos</h1>
            
            <!-- CREDENCIAIS DE DEMONSTRAÇÃO -->
            <div class="alert alert-info" style="margin-bottom: 1.5rem;">
                <h4 style="margin-bottom: 0.5rem; color: #2196F3;">🚀 Teste o Sistema</h4>
                <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">Use as credenciais abaixo para testar:</p>
                <div style="background: rgba(0, 0, 0, 0.3); padding: 0.75rem; border-radius: 6px; font-family: monospace;">
                    <div style="margin-bottom: 0.25rem;"><strong>Email:</strong> admin@carteira.com</div>
                    <div><strong>Senha:</strong> admin123</div>
                </div>
            </div>
            
            <!-- MOSTRAR MENSAGEM DE ERRO (se houver) -->
            <?php if($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- FORMULÁRIO DE LOGIN -->
            <!-- action="" significa que enviará para a mesma página -->
            <!-- method="POST" significa que dados serão enviados de forma segura -->
            <form method="POST" action="">
                <!-- CAMPO EMAIL -->
                <div class="form-group">
                    <label for="email" class="form-label">Email:</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control"
                        required 
                        placeholder="Digite seu email"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                    <!-- value="..." mantém o email digitado se deu erro -->
                </div>
                
                <!-- CAMPO SENHA -->
                <div class="form-group">
                    <label for="senha" class="form-label">Senha:</label>
                    <input 
                        type="password" 
                        id="senha" 
                        name="senha" 
                        class="form-control"
                        required 
                        placeholder="Digite sua senha"
                    >
                </div>
                
                <!-- BOTÃO PARA ENVIAR FORMULÁRIO -->
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Entrar
                </button>
            </form>
            
            <!-- LINK PARA REGISTRO (futuro) -->
            <div style="text-align: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #333;">
                <p class="text-secondary">Ainda não tem conta? <a href="register.php" class="text-primary-theme" style="text-decoration: none;">Cadastre-se aqui</a></p>
            </div>
        </div>
    </div>
</body>
</html>

<?php
/**
 * RESUMO DO ARQUIVO login.php:
 * 
 * FUNCIONALIDADES:
 * 1. Mostra formulário de login
 * 2. Processa dados do formulário
 * 3. Valida email e senha
 * 4. Faz login usando classe Auth
 * 5. Redireciona para dashboard se login OK
 * 6. Mostra erro se login falhou
 * 7. Redireciona para dashboard se já logado
 * 
 * CAMPOS DO FORMULÁRIO:
 * - email: Email do usuário
 * - senha: Senha do usuário
 * 
 * VALIDAÇÕES:
 * - Campos não podem estar vazios
 * - Email deve ter formato válido
 * - Credenciais devem existir no banco
 * 
 * SEGURANÇA:
 * - Usa htmlspecialchars() para prevenir XSS
 * - Usa trim() para limpar espaços
 * - Usa filter_var() para validar email
 * - Senhas são verificadas com password_verify()
 */
?> 