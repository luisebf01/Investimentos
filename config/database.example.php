<?php
/**
 * ARQUIVO DE CONFIGURAÇÃO DE EXEMPLO - NÃO EDITAR DIRETAMENTE!
 * 
 * 1. Copie este arquivo para: config/database.php
 * 2. Edite o arquivo database.php com suas credenciais reais
 * 3. NUNCA faça commit do arquivo database.php
 * 
 * SEGURANÇA: O arquivo database.php está no .gitignore
 */

class Database {
    // Configurações do banco de dados
    private $host = 'localhost';          // Host do banco (ex: localhost, 127.0.0.1)
    private $db_name = 'carteira_investimentos'; // Nome do banco de dados
    private $username = 'root';           // Usuário do banco (ex: root, admin)
    private $password = '';               // Senha do banco (MUDE ISSO!)
    
    public $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch(PDOException $exception) {
            echo "Erro de conexão: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

/*
=== INSTRUÇÕES DE CONFIGURAÇÃO ===

1. CONFIGURAÇÃO LOCAL (Desenvolvimento):
   - host: 'localhost'
   - username: 'root'
   - password: '' (vazio no Laragon/XAMPP)

2. CONFIGURAÇÃO DE PRODUÇÃO:
   - Use credenciais fornecidas pelo seu provedor de hospedagem
   - SEMPRE use senhas fortes
   - Configure SSL se possível

3. SEGURANÇA:
   - NUNCA versione suas credenciais reais
   - Use variáveis de ambiente em produção
   - Mantenha backups seguros do banco

4. TROUBLESHOOTING:
   - Erro 1049: Banco não existe → Execute setup_tables.php
   - Erro 1045: Credenciais inválidas → Verifique user/senha
   - Erro 2002: Servidor offline → Inicie MySQL no Laragon

=== EXEMPLO DE .ENV (Recomendado para produção) ===

DB_HOST=localhost
DB_NAME=carteira_investimentos
DB_USER=seu_usuario
DB_PASS=sua_senha_forte

=== COMO USAR .ENV ===

// No arquivo database.php use:
private $host = $_ENV['DB_HOST'] ?? 'localhost';
private $db_name = $_ENV['DB_NAME'] ?? 'carteira_investimentos';
private $username = $_ENV['DB_USER'] ?? 'root';
private $password = $_ENV['DB_PASS'] ?? '';

*/
?> 