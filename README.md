# 💰 Carteira Luxo - Sistema de Gerenciamento de Investimentos

Um sistema elegante e moderno para gerenciar sua carteira de investimentos, desenvolvido em PHP com MySQL e tema preto luxo.

## 🚀 Características

- **Design Luxuoso**: Interface moderna com tema preto e dourado
- **Multi-usuário**: Sistema de login e cadastro seguro
- **Gestão Completa**: Controle total dos seus investimentos
- **Dashboard Intuitivo**: Visualização clara de rendimentos e estatísticas
- **Responsivo**: Funciona perfeitamente em desktop e mobile
- **Categorização**: Organização por tipos de investimento (Ações, FIIs, Renda Fixa, etc.)

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL

## 🛠️ Instalação

### 1. Clone ou baixe os arquivos

Coloque todos os arquivos no diretório do seu servidor web (ex: `htdocs`, `www`, etc.)

### 2. Configure o banco de dados

1. Acesse seu MySQL e execute o arquivo `sql/setup.sql`:
```sql
mysql -u root -p < sql/setup.sql
```

2. Ou execute manualmente os comandos SQL do arquivo no phpMyAdmin ou cliente MySQL de sua preferência.

### 3. Configure a conexão

Edite o arquivo `config/database.php` com suas configurações:

```php
private $host = 'localhost';
private $db_name = 'carteira_investimentos';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 4. Configure permissões (Linux/Mac)

```bash
chmod 755 -R .
chmod 666 config/database.php
```

## 🎯 Uso

### Primeiro Acesso

1. Acesse `http://seudominio.com/` no navegador
2. Use a conta demo:
   - **Email**: admin@carteira.com
   - **Senha**: admin123

### Criando Conta

1. Clique em "Registre-se aqui" na tela de login
2. Preencha seus dados
3. Faça login com sua nova conta

### Adicionando Investimentos

1. No dashboard, clique em "Adicionar Investimento"
2. Preencha os dados:
   - Tipo de investimento
   - Nome (ex: "Banco do Brasil")
   - Ticker (ex: "BBAS3")
   - Quantidade de cotas/ações
   - Preço médio pago
   - Valor atual (opcional)
3. O sistema calcula automaticamente o rendimento

## 📊 Funcionalidades

### Dashboard
- Resumo financeiro completo
- Estatísticas de rendimento
- Distribuição por tipo de investimento
- Lista dos investimentos recentes

### Gestão de Investimentos
- Adicionar novos investimentos
- Editar investimentos existentes
- Visualizar histórico completo
- Cálculo automático de rendimentos

### Tipos de Investimento Suportados
- 📈 **Ações**: Ações de empresas na bolsa
- 🏢 **FIIs**: Fundos de Investimento Imobiliário
- 💰 **Renda Fixa**: CDB, LCI, LCA, Tesouro Direto
- ₿ **Criptomoedas**: Bitcoin, Ethereum, etc.
- 📊 **Fundos**: Fundos de investimento diversos
- 🔄 **Outros**: Outros tipos de investimento

## 🌐 Deploy em Servidor

### Hospedagem Compartilhada

1. Faça upload de todos os arquivos via FTP
2. Crie o banco de dados no painel de controle
3. Execute o script SQL
4. Configure as credenciais do banco

### VPS/Servidor Dedicado

1. Configure Apache/Nginx
2. Configure PHP e MySQL
3. Faça upload dos arquivos
4. Configure as permissões
5. Execute o setup do banco

### Exemplo de configuração Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Bloquear acesso direto a arquivos sensíveis
<Files "*.php">
    Order Deny,Allow
    Allow from all
</Files>

<FilesMatch "^(config|classes)/">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

## 🔒 Segurança

- Senhas criptografadas com hash seguro
- Proteção contra SQL Injection
- Validação de dados de entrada
- Controle de sessão
- Autenticação obrigatória

## 🎨 Personalização

### Modificando o Tema

Edite o arquivo `assets/css/style.css` para personalizar:

- Cores principais
- Gradientes
- Tipografia
- Layout

### Adicionando Novos Tipos

Execute no banco de dados:

```sql
INSERT INTO tipos_investimento (nome, descricao, cor) VALUES 
('Seu Tipo', 'Descrição do tipo', '#FF5722');
```

## 📱 Mobile

O sistema é totalmente responsivo e funciona perfeitamente em:
- Smartphones
- Tablets
- Desktop

## 🐛 Problemas Comuns

### Erro de Conexão com Banco
- Verifique as credenciais em `config/database.php`
- Confirme se o MySQL está rodando
- Verifique se o banco de dados foi criado

### Página em Branco
- Verifique os logs de erro do PHP
- Confirme se todas as extensões estão instaladas
- Verifique as permissões dos arquivos

### CSS não Carrega
- Verifique se o arquivo `assets/css/style.css` existe
- Confirme o caminho relativo
- Limpe o cache do navegador

## 🤝 Suporte

Para dúvidas ou problemas:

1. Verifique se seguiu todos os passos de instalação
2. Consulte os logs de erro
3. Verifique as permissões de arquivo
4. Confirme a configuração do banco de dados

## 📄 Licença

Este projeto é um sistema personalizado desenvolvido para gerenciamento de carteira de investimentos.

---

**Desenvolvido com ❤️ para gestão profissional de investimentos** 