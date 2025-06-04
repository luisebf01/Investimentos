<?php
/**
 * CLASSE DE AUTENTICAÇÃO (LOGIN/LOGOUT)
 * 
 * Esta classe é responsável por gerenciar tudo relacionado ao login dos usuários:
 * - Fazer login (verificar email e senha)
 * - Fazer logout (sair do sistema)
 * - Verificar se o usuário está logado
 * - Obter informações do usuário atual
 * - Registrar novos usuários
 */

// Incluir o arquivo de conexão com o banco de dados
require_once 'config/database.php';

class Auth {
    // Variável privada que guarda a conexão com o banco de dados
    // "private" significa que só pode ser usada dentro desta classe
    private $conn;
    
    /**
     * CONSTRUTOR - Executado automaticamente quando a classe é criada
     * É como se fosse a "inicialização" da classe
     */
    public function __construct() {
        // Criar uma nova conexão com o banco de dados
        $database = new Database();
        $this->conn = $database->getConnection();
        
        // Garantir que a sessão esteja iniciada
        // Sessão é como uma "memória temporária" que guarda dados do usuário enquanto navega
        $this->ensureSession();
    }
    
    /**
     * MÉTODO PRIVADO - Garante que a sessão esteja ativa
     * Sessão é necessária para "lembrar" que o usuário está logado
     */
    private function ensureSession() {
        // Verificar se a sessão ainda não foi iniciada
        if (session_status() == PHP_SESSION_NONE) {
            // Iniciar a sessão (como abrir um "cofre de memória temporária")
            session_start();
        }
    }
    
    /**
     * FAZER LOGIN
     * 
     * @param string $email - Email digitado pelo usuário
     * @param string $senha - Senha digitada pelo usuário
     * @return bool - true se login foi bem sucedido, false caso contrário
     */
    public function login($email, $senha) {
        // Preparar uma consulta SQL para buscar o usuário no banco
        // ":email" é um placeholder (espaço reservado) que será preenchido depois
        $query = "SELECT id, nome, email, senha FROM usuarios WHERE email = :email AND ativo = 1";
        
        // Preparar a consulta (como preparar uma pergunta para o banco)
        $stmt = $this->conn->prepare($query);
        
        // Substituir o ":email" pelo email real digitado pelo usuário
        // Isso previne ataques SQL Injection (tentativas de hackear o banco)
        $stmt->bindParam(':email', $email);
        
        // Executar a consulta no banco de dados
        $stmt->execute();
        
        // Verificar se encontrou algum usuário com esse email
        if($stmt->rowCount() > 0) {
            // Buscar os dados do usuário encontrado
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar se a senha digitada confere com a senha salva no banco
            // password_verify é uma função segura que compara senhas criptografadas
            if(password_verify($senha, $row['senha'])) {
                // SUCESSO! Senha está correta
                
                // Garantir que a sessão esteja ativa
                $this->ensureSession();
                
                // Salvar informações do usuário na sessão (memória temporária)
                // $_SESSION é como uma "gaveta" que guarda dados enquanto o usuário navega
                $_SESSION['user_id'] = $row['id'];           // ID do usuário
                $_SESSION['user_name'] = $row['nome'];       // Nome do usuário
                $_SESSION['user_email'] = $row['email'];     // Email do usuário
                
                // Retornar true = login bem sucedido
                return true;
            }
        }
        
        // Se chegou aqui, email não existe ou senha está errada
        return false;
    }
    
    /**
     * FAZER LOGOUT (sair do sistema)
     * Remove todos os dados da sessão e "esquece" que o usuário estava logado
     */
    public function logout() {
        // Garantir que a sessão esteja ativa
        $this->ensureSession();
        
        // Destruir completamente a sessão (como apagar a "memória temporária")
        session_destroy();
        
        return true;
    }
    
    /**
     * VERIFICAR SE O USUÁRIO ESTÁ LOGADO
     * 
     * @return bool - true se está logado, false se não está
     */
    public function isLoggedIn() {
        // Garantir que a sessão esteja ativa
        $this->ensureSession();
        
        // Verificar se existe o ID do usuário na sessão
        // Se existe = usuário está logado
        // Se não existe = usuário não está logado
        return isset($_SESSION['user_id']);
    }
    
    /**
     * OBTER DADOS DO USUÁRIO ATUAL
     * 
     * @return array|null - Array com dados do usuário ou null se não estiver logado
     */
    public function getCurrentUser() {
        // Primeiro verificar se o usuário está logado
        if($this->isLoggedIn()) {
            // Retornar um array (lista) com os dados do usuário
            return [
                'id' => $_SESSION['user_id'],      // ID único do usuário
                'nome' => $_SESSION['user_name'],  // Nome completo
                'email' => $_SESSION['user_email'] // Email
            ];
        }
        
        // Se não está logado, retornar null (nada)
        return null;
    }
    
    /**
     * REGISTRAR NOVO USUÁRIO
     * 
     * @param string $nome - Nome completo do usuário
     * @param string $email - Email do usuário
     * @param string $senha - Senha do usuário
     * @return bool - true se registro foi bem sucedido, false se email já existe
     */
    public function register($nome, $email, $senha) {
        // PRIMEIRO: Verificar se o email já está sendo usado por outro usuário
        $query = "SELECT id FROM usuarios WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        // Se encontrou algum usuário com esse email
        if($stmt->rowCount() > 0) {
            // Email já existe, não pode registrar
            return false;
        }
        
        // SEGUNDO: Criar o novo usuário
        
        // Criptografar a senha antes de salvar no banco
        // NUNCA salvar senhas em texto puro por segurança!
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Preparar comando SQL para inserir novo usuário
        $query = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $this->conn->prepare($query);
        
        // Preencher os dados
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_hash); // Senha criptografada
        
        // Tentar executar o comando
        return $stmt->execute(); // Retorna true se deu certo, false se deu erro
    }
    
    /**
     * FORÇAR LOGIN - Redireciona para login se usuário não estiver logado
     * Use este método nas páginas que só usuários logados podem acessar
     */
    public function requireLogin() {
        // Verificar se o usuário NÃO está logado
        if(!$this->isLoggedIn()) {
            // Redirecionar para a página de login
            header('Location: login.php');
            exit(); // Parar a execução do resto da página
        }
        
        // Se chegou aqui, usuário está logado e pode continuar
    }
}

/**
 * RESUMO DA CLASSE Auth:
 * 
 * 1. login($email, $senha) - Faz login do usuário
 * 2. logout() - Faz logout do usuário  
 * 3. isLoggedIn() - Verifica se está logado
 * 4. getCurrentUser() - Pega dados do usuário atual
 * 5. register($nome, $email, $senha) - Registra novo usuário
 * 6. requireLogin() - Força login (redireciona se não logado)
 * 
 * COMO USAR:
 * $auth = new Auth();
 * $auth->login('email@exemplo.com', 'senha123');
 * $usuario = $auth->getCurrentUser();
 * $auth->logout();
 */
?> 