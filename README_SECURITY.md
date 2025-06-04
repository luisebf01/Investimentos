# 🔒 Guia de Segurança - Carteira de Investimentos

## ⚠️ IMPORTANTE - LEIA ANTES DE USAR

Este projeto lida com **dados financeiros sensíveis**. Siga rigorosamente as práticas de segurança abaixo.

## 🛡️ Configuração Segura

### 1. Clone e Configuração Inicial

```bash
# Clone o repositório
git clone [seu-repositorio]
cd carteira-investimentos

# Copie o arquivo de configuração
cp config/database.example.php config/database.php
```

### 2. Configure o Banco de Dados

1. **Edite** `config/database.php` com suas credenciais reais
2. **NUNCA** faça commit deste arquivo
3. Use senhas fortes em produção

```php
// Em config/database.php (NÃO VERSIONAR!)
private $host = 'localhost';
private $db_name = 'carteira_investimentos';
private $username = 'seu_usuario';
private $password = 'sua_senha_FORTE';
```

### 3. Execute o Setup do Banco

```bash
# Execute uma vez para criar as tabelas
php setup_database.php
```

## 🚨 Arquivos Sensíveis (PROTEGIDOS pelo .gitignore)

- `config/database.php` - **Credenciais do banco**
- `*.log` - **Logs do sistema**
- `backups/` - **Backups do banco**
- `uploads/` - **Arquivos dos usuários**
- `.env` - **Variáveis de ambiente**

## 🔐 Práticas de Segurança

### Para Desenvolvimento

```bash
# Sempre verificar antes de commit
git status

# Verificar se database.php não está sendo versionado
git check-ignore config/database.php
# Deve retornar: config/database.php
```

### Para Produção

1. **Use HTTPS obrigatório**
2. **Configure SSL no banco de dados**
3. **Use senhas complexas**
4. **Habilite logs de auditoria**
5. **Configure backups automáticos**
6. **Use variáveis de ambiente**

```bash
# Exemplo de .env para produção
DB_HOST=seu-servidor-seguro.com
DB_NAME=carteira_prod
DB_USER=usuario_limitado
DB_PASS=SenhaSuper#Forte!2024
```

## 🚫 O QUE NUNCA FAZER

- ❌ **NUNCA** commitar `config/database.php`
- ❌ **NUNCA** usar senhas vazias em produção
- ❌ **NUNCA** expor logs publicamente
- ❌ **NUNCA** versionar backups do banco
- ❌ **NUNCA** usar credenciais reais em exemplos

## ✅ Checklist de Segurança

Antes de fazer deploy:

- [ ] Configuração do banco em arquivo separado
- [ ] Senhas fortes configuradas
- [ ] HTTPS habilitado
- [ ] Logs protegidos
- [ ] Backups configurados
- [ ] .gitignore verificado
- [ ] Variáveis de ambiente configuradas
- [ ] Acesso ao banco limitado

## 🔍 Verificação de Segurança

```bash
# Verificar se há arquivos sensíveis sendo versionados
git ls-files | grep -E "(database\.php|\.log|\.env)"
# Não deve retornar nada

# Verificar .gitignore
cat .gitignore | grep database.php
# Deve mostrar: config/database.php
```

## 📞 Em Caso de Vazamento

Se você acidentalmente commitou informações sensíveis:

1. **MUDE IMEDIATAMENTE** todas as senhas
2. **Revogue** chaves de API expostas
3. **Rewrite** do histórico Git se necessário
4. **Notifique** a equipe

```bash
# Remove arquivo do histórico (USE COM CUIDADO!)
git filter-branch --force --index-filter 'git rm --cached --ignore-unmatch config/database.php' --prune-empty --tag-name-filter cat -- --all
```

## 🛠️ Ferramentas Recomendadas

- **git-secrets** - Previne commits de segredos
- **GitGuardian** - Monitoramento contínuo
- **Vault** - Gerenciamento de segredos
- **1Password/Bitwarden** - Gerenciador de senhas

## 📚 Recursos Adicionais

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [MySQL Security Guide](https://dev.mysql.com/doc/refman/8.0/en/security.html)

---

**🚨 LEMBRE-SE: A segurança é responsabilidade de todos!**

Se tiver dúvidas sobre segurança, **SEMPRE** pergunte antes de fazer commit. 