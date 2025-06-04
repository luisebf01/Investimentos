# 📊 Sistema de Auditoria e Histórico de Transações

## Visão Geral

O sistema de auditoria foi implementado para registrar e rastrear todas as ações dos usuários no sistema de carteira de investimentos. Ele fornece um histórico completo de transações, criações, edições, exclusões e sessões de usuário.

## 🗂️ Estrutura do Sistema

### Arquivos Criados/Modificados

1. **`sql/audit_system.sql`** - Script SQL para criar as tabelas de auditoria
2. **`classes/AuditLog.php`** - Classe principal para gerenciar logs de auditoria
3. **`historico.php`** - Página para visualizar o histórico de transações
4. **Integração em todos os arquivos CRUD** - Logs automáticos em operações

### Tabelas do Banco de Dados

#### `audit_logs` - Log Principal de Auditoria
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FK para usuarios)
- acao (ENUM: 'create', 'update', 'delete', 'login', 'logout')
- tabela_afetada (VARCHAR: nome da tabela)
- registro_id (INT: ID do registro afetado)
- dados_anteriores (JSON: dados antes da alteração)
- dados_novos (JSON: dados após a alteração)
- ip_address (VARCHAR: IP do usuário)
- user_agent (TEXT: navegador/dispositivo)
- detalhes (TEXT: descrição da ação)
- data_acao (TIMESTAMP: quando ocorreu)
```

#### `sessoes_usuario` - Controle de Sessões
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FK para usuarios)
- session_id (VARCHAR: ID da sessão PHP)
- ip_address (VARCHAR: IP do login)
- user_agent (TEXT: navegador/dispositivo)
- data_login (TIMESTAMP: quando fez login)
- data_ultimo_acesso (TIMESTAMP: última atividade)
- data_logout (TIMESTAMP: quando fez logout)
- ativo (BOOLEAN: se sessão está ativa)
```

#### `audit_operacoes_financeiras` - Log Específico de Operações Financeiras
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FK para usuarios)
- tipo_operacao (ENUM: tipos específicos de operações)
- registro_id (INT: ID do registro afetado)
- valor_anterior (DECIMAL: valor antes da alteração)
- valor_novo (DECIMAL: valor após a alteração)
- quantidade_anterior (DECIMAL: quantidade antes)
- quantidade_nova (DECIMAL: quantidade após)
- descricao (TEXT: descrição da operação)
- ip_address (VARCHAR: IP do usuário)
- data_operacao (TIMESTAMP: quando ocorreu)
```

#### `v_audit_resumo` - View para Consultas Simplificadas
View que junta dados de auditoria com informações dos registros afetados.

## 🔧 Funcionalidades Implementadas

### 1. Registro Automático de Ações

**Investimentos:**
- ✅ Criação de novos investimentos
- ✅ Edição de investimentos existentes
- ✅ Exclusão de investimentos

**Metas:**
- ✅ Criação de novas metas
- ✅ Edição de metas existentes
- ✅ Exclusão de metas

**Sessões:**
- ✅ Login de usuários
- ✅ Logout de usuários
- ✅ Controle de sessões ativas

### 2. Página de Histórico (`historico.php`)

**Características:**
- 📊 Estatísticas rápidas de atividade
- 🔍 Filtros por ação, tabela e período
- 📋 Duas abas: Histórico Geral e Operações Financeiras
- 📄 Paginação para grandes volumes de dados
- 📱 Design responsivo

**Filtros Disponíveis:**
- **Por Ação:** Create, Update, Delete, Login, Logout
- **Por Tabela:** Investimentos, Metas, Sessões
- **Por Período:** 7 dias, 30 dias, 90 dias, 1 ano

### 3. Classe AuditLog

**Métodos Principais:**

```php
// Registrar ação geral
registrarAcao($usuario_id, $acao, $tabela, $registro_id, $dados_anteriores, $dados_novos, $detalhes)

// Registrar operação financeira específica
registrarOperacaoFinanceira($usuario_id, $tipo_operacao, $registro_id, $valor_anterior, $valor_novo, ...)

// Registrar login
registrarLogin($usuario_id, $session_id)

// Registrar logout
registrarLogout($usuario_id, $session_id)

// Métodos convenientes
logInvestimento($usuario_id, $acao, $investimento_id, $dados_anteriores, $dados_novos)
logMeta($usuario_id, $acao, $meta_id, $dados_anteriores, $dados_novos)

// Consultas
getHistoricoUsuario($usuario_id, $limite, $offset, $filtro_acao, $filtro_tabela)
getOperacoesFinanceiras($usuario_id, $limite)
getEstatisticasAtividade($usuario_id, $dias)
```

## 🚀 Como Usar

### 1. Instalação

Execute o script SQL para criar as tabelas:
```sql
SOURCE sql/audit_system.sql;
```

### 2. Integração Automática

O sistema já está integrado em todos os arquivos CRUD. As ações são registradas automaticamente quando:
- Usuário faz login/logout
- Investimento é criado/editado/excluído
- Meta é criada/editada/excluída

### 3. Visualização do Histórico

Acesse `historico.php` através do menu de navegação ou diretamente pela URL.

## 📈 Informações Registradas

### Para Cada Ação:
- **Quem:** ID do usuário que executou
- **O que:** Tipo de ação (create/update/delete/login/logout)
- **Onde:** Tabela/registro afetado
- **Quando:** Data e hora exata
- **Como:** IP, navegador, detalhes técnicos
- **Dados:** Estado anterior e novo (para updates)

### Operações Financeiras:
- Valores antes e depois das alterações
- Quantidades alteradas
- Tipo específico da operação
- Descrição detalhada

## 🔒 Segurança e Privacidade

### Medidas Implementadas:
- ✅ Logs isolados por usuário (cada usuário vê apenas seus logs)
- ✅ Captura de IP real (mesmo atrás de proxies)
- ✅ Validação de sessões
- ✅ Sanitização de dados exibidos
- ✅ Limpeza automática de logs antigos

### Limpeza Automática:
- **Logs gerais:** 6 meses
- **Sessões:** 3 meses  
- **Operações financeiras:** 12 meses
- **Evento automático:** Executa mensalmente

## 🎨 Interface do Usuário

### Design:
- 🌙 Tema escuro consistente com o sistema
- 📊 Cards de estatísticas visuais
- 🏷️ Badges coloridos por tipo de ação
- 📱 Layout responsivo para mobile
- 🔍 Filtros intuitivos
- 📄 Paginação eficiente

### Cores dos Badges:
- **CREATE:** Verde (sucesso)
- **UPDATE:** Amarelo (atenção)
- **DELETE:** Vermelho (perigo)
- **LOGIN:** Azul (informação)
- **LOGOUT:** Cinza (neutro)

## 🔧 Manutenção

### Limpeza Manual:
```sql
CALL LimparLogsAntigos();
```

### Consultas Úteis:
```sql
-- Ver logs de um usuário específico
SELECT * FROM v_audit_resumo WHERE usuario_id = 1 ORDER BY data_acao DESC;

-- Estatísticas de atividade
SELECT acao, COUNT(*) as total FROM audit_logs GROUP BY acao;

-- Sessões ativas
SELECT * FROM sessoes_usuario WHERE ativo = TRUE;
```

## 📊 Relatórios Disponíveis

### Na Página de Histórico:
1. **Total de ações** realizadas pelo usuário
2. **Atividade recente** no período selecionado
3. **Operações financeiras** com valores
4. **Ação mais comum** do usuário

### Dados Exportáveis:
- Histórico completo em formato tabular
- Operações financeiras detalhadas
- Estatísticas de uso

## 🚀 Próximas Melhorias

### Funcionalidades Futuras:
- [ ] Exportação de relatórios em PDF/Excel
- [ ] Alertas por email para ações críticas
- [ ] Dashboard de administração
- [ ] Logs de tentativas de acesso inválidas
- [ ] Backup automático de logs
- [ ] API para consulta de logs
- [ ] Integração com sistemas externos

### Otimizações:
- [ ] Índices adicionais para performance
- [ ] Compressão de dados antigos
- [ ] Cache de consultas frequentes
- [ ] Arquivamento de logs históricos

## 📞 Suporte

Para dúvidas ou problemas com o sistema de auditoria:
1. Verifique os logs de erro do PHP
2. Confirme se as tabelas foram criadas corretamente
3. Teste as permissões de banco de dados
4. Verifique se a classe AuditLog está sendo incluída

---

**Desenvolvido para Carteira Luxo - Sistema de Gestão de Investimentos**
*Versão 1.0 - Sistema de Auditoria Completo* 