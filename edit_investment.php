<?php
/**
 * PÁGINA DE EDIÇÃO DE INVESTIMENTOS
 * 
 * Esta página permite editar um investimento existente.
 * Utiliza a classe Investment para buscar e atualizar dados.
 */

// Incluir classes necessárias
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';
require_once 'classes/AuditLog.php';

// Criar instâncias das classes
$auth = new Auth();
$investment = new Investment();
$auditLog = new AuditLog();

// VERIFICAR SE USUÁRIO ESTÁ LOGADO
// Redirecionar para login se não estiver
$auth->requireLogin();

// OBTER DADOS DO USUÁRIO ATUAL
$user = $auth->getCurrentUser();

// Inicializar variáveis
$error = '';
$success = '';
$investmentData = null;

// Verificar se foi passado um ID válido
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: investments.php');
    exit();
}

$id = (int)$_GET['id'];

// Buscar dados do investimento
$investmentData = $investment->getById($id, $user['id']);

if(!$investmentData) {
    header('Location: investments.php');
    exit();
}

// Verificar se o investimento pertence ao usuário logado
if($investmentData['usuario_id'] != $user['id']) {
    header('Location: investments.php');
    exit();
}

// Buscar tipos de investimento para o dropdown
$tipos = $investment->getTypes();

// PROCESSAR FORMULÁRIO DE EDIÇÃO
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pegar dados do formulário
    $nome = trim($_POST['nome']);
    $tipo_id = (int)$_POST['tipo_id'];
    $ticker = trim($_POST['ticker']);
    $quantidade = (float)$_POST['quantidade'];
    $preco_medio = (float)$_POST['preco_medio'];
    $valor_atual = (float)$_POST['valor_atual'];
    $data_compra = $_POST['data_compra'];
    $observacoes = trim($_POST['observacoes']);
    
    // VALIDAÇÕES
    if(empty($nome)) {
        $error = 'Nome do investimento é obrigatório.';
    } elseif($tipo_id <= 0) {
        $error = 'Selecione um tipo de investimento válido.';
    } elseif($quantidade <= 0) {
        $error = 'Quantidade deve ser maior que zero.';
    } elseif($preco_medio <= 0) {
        $error = 'Preço médio deve ser maior que zero.';
    } elseif($valor_atual < 0) {
        $error = 'Valor atual não pode ser negativo.';
    } elseif(empty($data_compra)) {
        $error = 'Data de compra é obrigatória.';
    } else {
        // Calcular valores automaticamente
        $valor_investido = $quantidade * $preco_medio;
        $rendimento = $valor_atual - $valor_investido;
        $percentual_rendimento = $valor_investido > 0 ? ($rendimento / $valor_investido) * 100 : 0;
        
        // Preparar dados para atualização
        $data = [
            'usuario_id' => $user['id'],
            'nome' => $nome,
            'tipo_id' => $tipo_id,
            'ticker' => $ticker,
            'quantidade' => $quantidade,
            'preco_medio' => $preco_medio,
            'valor_investido' => $valor_investido,
            'valor_atual' => $valor_atual,
            'rendimento' => $rendimento,
            'percentual_rendimento' => $percentual_rendimento,
            'data_compra' => $data_compra,
            'observacoes' => $observacoes
        ];
        
        // Tentar atualizar o investimento
        if($investment->update($id, $data)) {
            // Registrar no log de auditoria
            $auditLog->logInvestimento($user['id'], 'update', $id, $investmentData, $data);
            
            $success = 'Investimento atualizado com sucesso!';
            // Recarregar dados atualizados
            $investmentData = $investment->getById($id, $user['id']);
        } else {
            $error = 'Erro ao atualizar investimento. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Investimento - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--primary-light);
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: var(--bg-input);
            border: 1px solid var(--border-light);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 1rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px var(--primary-focus);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-medium));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px var(--primary-shadow);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
            <!-- CABEÇALHO DA PÁGINA -->
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>✏️ Editar Investimento</h1>
                    <p class="text-secondary">Atualize as informações do seu investimento</p>
                </div>
                <a href="investments.php" class="btn btn-secondary">
                    ← Voltar para Investimentos
                </a>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- FORMULÁRIO PRINCIPAL -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Dados do Investimento</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-row">
                            <!-- Nome do Investimento -->
                            <div class="form-group">
                                <label for="nome" class="form-label">Nome do Investimento *</label>
                                <input type="text" id="nome" name="nome" class="form-control" 
                                       value="<?php echo htmlspecialchars($investmentData['nome']); ?>" 
                                       required placeholder="Ex: PETR4, Nubank, etc.">
                            </div>

                            <!-- Tipo de Investimento -->
                            <div class="form-group">
                                <label for="tipo_id" class="form-label">Tipo de Investimento *</label>
                                <select id="tipo_id" name="tipo_id" class="form-control form-select" required>
                                    <option value="">Selecione o tipo</option>
                                    <?php foreach($tipos as $tipo): ?>
                                        <option value="<?php echo $tipo['id']; ?>" 
                                                <?php echo $tipo['id'] == $investmentData['tipo_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <!-- Ticker/Código -->
                            <div class="form-group">
                                <label for="ticker" class="form-label">Ticker/Código</label>
                                <input type="text" id="ticker" name="ticker" class="form-control" 
                                       value="<?php echo htmlspecialchars($investmentData['ticker']); ?>" 
                                       placeholder="Ex: PETR4, BTCUSD">
                            </div>

                            <!-- Data de Compra -->
                            <div class="form-group">
                                <label for="data_compra" class="form-label">Data da Compra *</label>
                                <input type="date" id="data_compra" name="data_compra" class="form-control" 
                                       value="<?php echo $investmentData['data_compra']; ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <!-- Quantidade -->
                            <div class="form-group">
                                <label for="quantidade" class="form-label">Quantidade *</label>
                                <input type="number" id="quantidade" name="quantidade" class="form-control" 
                                       value="<?php echo $investmentData['quantidade']; ?>" 
                                       step="0.000001" min="0.000001" required placeholder="Ex: 100">
                            </div>

                            <!-- Preço Médio -->
                            <div class="form-group">
                                <label for="preco_medio" class="form-label">Preço Médio (R$) *</label>
                                <input type="number" id="preco_medio" name="preco_medio" class="form-control" 
                                       value="<?php echo $investmentData['preco_medio']; ?>" 
                                       step="0.01" min="0.01" required placeholder="Ex: 25.50">
                            </div>
                        </div>

                        <div class="form-row">
                            <!-- Valor Atual Total -->
                            <div class="form-group">
                                <label for="valor_atual" class="form-label">Valor Atual Total (R$) *</label>
                                <input type="number" id="valor_atual" name="valor_atual" class="form-control" 
                                       value="<?php echo $investmentData['valor_atual']; ?>" 
                                       step="0.01" min="0" required placeholder="Ex: 2600.00">
                            </div>

                            <!-- Valor Investido (Calculado) -->
                            <div class="form-group">
                                <label class="form-label">Valor Inicial Investido (R$)</label>
                                <input type="text" class="form-control" 
                                       value="R$ <?php echo number_format($investmentData['valor_investido'], 2, ',', '.'); ?>" 
                                       readonly style="background: #2a2a2a; color: #ccc;">
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="form-group">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea style="resize: none;"
                                      id="observacoes" 
                                      name="observacoes" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Anotações adicionais sobre este investimento..."><?php echo htmlspecialchars($investmentData['observacoes']); ?></textarea>
                        </div>

                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">💾 Salvar Alterações</button>
                            <a href="investments.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 