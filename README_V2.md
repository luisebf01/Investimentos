# 💰 Carteira Luxo 2.0 - Sistema Avançado de Investimentos

## 🚀 **O QUE HÁ DE NOVO NA VERSÃO 2.0**

A versão 2.0 da Carteira Luxo foi **completamente modernizada** para uso pessoal, incluindo funcionalidades profissionais que você encontraria apenas em plataformas premium.

---

## ⭐ **PRINCIPAIS MELHORIAS**

### 📊 **1. DASHBOARD INTERATIVO COM GRÁFICOS**
- **Gráfico de Pizza**: Distribuição automática por tipo de investimento
- **Gráfico de Performance**: Evolução da carteira ao longo do tempo
- **Animações Suaves**: Interface moderna e responsiva
- **Cards Estatísticos**: Métricas importantes em tempo real

### 💱 **2. COTAÇÕES AUTOMÁTICAS EM TEMPO REAL**
- **APIs Brasileiras Gratuitas**: Brapi.dev para ações e FIIs
- **Criptomoedas**: Integração com CoinGecko
- **Atualização com 1 Clique**: Botão para atualizar todas as cotações
- **Cálculo Automático**: Rendimentos atualizados automaticamente
- **Notificações**: Sistema de alertas visuais

### 🎯 **3. SISTEMA DE METAS PESSOAIS**
- **Objetivos Financeiros**: Defina metas de valor total, aportes mensais ou rendimento
- **Acompanhamento Visual**: Barras de progresso animadas
- **Múltiplas Metas**: Quantas metas quiser simultâneamente
- **Conquistas**: Notificações quando atingir objetivos
- **Datas Limite**: Prazo para alcançar suas metas

### 📈 **4. HISTÓRICO E EVOLUÇÃO DA CARTEIRA**
- **Snapshots Diários**: Sistema para armazenar evolução histórica
- **Gráficos Temporais**: Visualizar crescimento ao longo do tempo
- **Análise de Performance**: Comparar períodos diferentes
- **Backup Automático**: Preservar histórico de dados

### 📊 **5. RELATÓRIOS AVANÇADOS E BACKUP**
- **Backup Completo JSON**: Todos os dados em formato estruturado
- **Exportação CSV/Excel**: Para análises externas
- **Relatório para IR**: Formato específico para Imposto de Renda
- **Análises Estatísticas**: Melhor/pior investimento, diversificação
- **Função Imprimir**: Relatórios em PDF via navegador

### 🛡️ **6. SEGURANÇA E PERFORMANCE**
- **APIs Otimizadas**: Requisições em lote para melhor performance
- **Cache de Dados**: Evitar requisições desnecessárias
- **Validação Avançada**: Proteção contra erros de entrada
- **Logs de Erro**: Sistema robusto de tratamento de erros

---

## 🔧 **COMO ATUALIZAR PARA A VERSÃO 2.0**

### **1. Backup dos Dados Atuais**
```bash
# Faça backup do banco de dados atual
mysqldump -u root -p carteira_investimentos > backup_v1.sql
```

### **2. Atualizar Estrutura do Banco**
```sql
-- Execute o script de upgrade
mysql -u root -p carteira_investimentos < sql/upgrade_v2.sql
```

### **3. Verificar Novos Arquivos**
Certifique-se que os novos arquivos estão presentes:
- `classes/PriceUpdater.php` - Sistema de cotações
- `api/update_prices.php` - Endpoint para atualizações
- `metas.php` - Página de metas
- `relatorios.php` - Página de relatórios
- `sql/upgrade_v2.sql` - Script de atualização

### **4. Testar Funcionalidades**
1. Acesse o dashboard e veja os novos gráficos
2. Teste o botão "Atualizar Cotações"
3. Crie uma meta em `metas.php`
4. Faça um backup em `relatorios.php`

---

## 📱 **NAVEGAÇÃO ATUALIZADA**

A navegação agora inclui as novas páginas:

```
💰 Carteira
├── 📊 Dashboard (com gráficos interativos)
├── 💼 Investimentos
├── ➕ Adicionar
├── 🎯 Metas (NOVO!)
├── 📊 Relatórios (NOVO!)
├── 👤 Perfil
└── 🚪 Sair
```

---

## 🎯 **COMO USAR AS NOVAS FUNCIONALIDADES**

### **📊 Dashboard Interativo**
1. **Gráficos automáticos** aparecem quando você tem investimentos
2. **Clique em "Atualizar Cotações"** para buscar preços atuais
3. **Animações suaves** melhoram a experiência visual

### **🎯 Sistema de Metas**
1. Acesse `Metas` no menu
2. Clique em "Criar Nova Meta"
3. Defina:
   - **Título**: Ex: "Primeira casa própria"
   - **Valor**: Ex: R$ 100.000,00
   - **Tipo**: Valor total, aporte mensal ou % rendimento
   - **Data limite** (opcional)
4. Acompanhe o progresso automaticamente

### **💱 Cotações Automáticas**
1. **Certifique-se** que seus investimentos têm ticker preenchido
2. **Clique** no botão "🔄 Atualizar Cotações" no dashboard
3. **Aguarde** o processamento (poucos segundos)
4. **Veja** os valores atualizados automaticamente

### **📊 Relatórios e Backup**
1. Acesse `Relatórios` no menu
2. Opções disponíveis:
   - **📋 Backup JSON**: Backup completo dos dados
   - **📊 Exportar CSV**: Para Excel/Google Sheets
   - **🧾 Relatório IR**: Para declaração de imposto
   - **🖨️ Imprimir**: Relatório em PDF

---

## 🔄 **AUTOMAÇÃO E MANUTENÇÃO**

### **Snapshots Automáticos (Opcional)**
Para histórico automático, configure um cron job:

```bash
# Adicione ao crontab para executar diariamente às 23:59
59 23 * * * mysql -u root -p carteira_investimentos -e "CALL CriarSnapshotCarteira(1);"
```

### **Backup Automático**
Configure backup semanal:

```bash
# Backup automático toda segunda às 02:00
0 2 * * 1 mysqldump -u root -p carteira_investimentos > /backups/carteira_$(date +\%Y\%m\%d).sql
```

---

## 🎨 **PERSONALIZAÇÕES DISPONÍVEIS**

### **Cores dos Gráficos**
Edite as cores no arquivo `dashboard.php`:
```javascript
backgroundColor: [
    '#FFD700', // Dourado
    '#FF6B6B', // Vermelho
    '#4ECDC4', // Azul água
    // Adicione suas cores preferidas
]
```

### **Tipos de Investimento**
Adicione novos tipos no banco:
```sql
INSERT INTO tipos_investimento (nome, descricao, cor) VALUES 
('Tesouro Direto', 'Títulos públicos', '#9C27B0');
```

### **APIs de Cotação**
Para adicionar mais APIs, edite `classes/PriceUpdater.php`

---

## 🚨 **SOLUÇÃO DE PROBLEMAS**

### **Cotações não atualizam**
1. Verifique se os tickers estão corretos (ex: PETR4, VALE3)
2. Confirme se há conexão com internet
3. Veja o console do navegador para erros

### **Gráficos não aparecem**
1. Verifique se há investimentos cadastrados
2. Confirme se Chart.js está carregando
3. Limpe o cache do navegador

### **Erro ao criar metas**
1. Certifique-se que executou o `upgrade_v2.sql`
2. Verifique permissões do banco de dados
3. Confirme que a tabela `metas_investimento` existe

### **Backup não funciona**
1. Verifique permissões de escrita
2. Confirme configuração do PHP (file_get_contents, fopen)
3. Teste com dados simples primeiro

---

## 📈 **PRÓXIMAS FUNCIONALIDADES (Roadmap)**

- **PWA (App Mobile)**: Instalar como aplicativo
- **Notificações Push**: Alertas de preços no celular
- **Análise Técnica**: RSI, Médias Móveis, Suporte/Resistência
- **API de Dividendos**: Acompanhar rendimentos de FIIs e ações
- **Simulador**: Testar estratégias sem dinheiro real
- **Integração Bancária**: Open Banking para sincronização automática

---

## 🤝 **SUPORTE E FEEDBACK**

A versão 2.0 foi projetada para uso pessoal profissional. Para dúvidas:

1. **Consulte este README** primeiro
2. **Verifique os logs** de erro do PHP
3. **Teste com dados simples** para isolar problemas
4. **Faça backup** antes de grandes mudanças

---

## 📄 **LICENÇA E CRÉDITOS**

- **Desenvolvido para uso pessoal**
- **APIs utilizadas**: Brapi.dev (ações/FIIs), CoinGecko (cripto)
- **Bibliotecas**: Chart.js para gráficos
- **Design**: Tema dark luxo com gradientes dourados

---

**🎉 Parabéns! Agora você tem uma plataforma de investimentos completa e profissional para gerenciar sua carteira pessoal!**

---

*Versão 2.0 - Novembro 2024* 