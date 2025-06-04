<?php
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';
require_once 'classes/AuditLog.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$investment = new Investment();
$auditLog = new AuditLog();
$types = $investment->getTypes();

$error = '';
$success = '';

// Processar formulário
if($_POST) {
    $data = [
        'usuario_id' => $user['id'],
        'tipo_investimento' => $_POST['tipo_id'] ?? '',
        'nome' => $_POST['nome'] ?? '',
        'ticker' => $_POST['ticker'] ?? '',
        'quantidade' => floatval($_POST['quantidade'] ?? 0),
        'preco_medio' => floatval($_POST['preco_medio'] ?? 0),
        'valor_atual' => floatval($_POST['valor_atual'] ?? 0),
        'data_compra' => $_POST['data_compra'] ?? '',
        'observacoes' => $_POST['observacoes'] ?? ''
    ];
    
    if(empty($data['tipo_investimento']) || empty($data['nome']) || $data['quantidade'] <= 0 || $data['preco_medio'] <= 0) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $id = $investment->create($data);
        if($id) {
            // Registrar no log de auditoria
            $auditLog->logInvestimento($user['id'], 'create', $id, null, $data);
            
            $success = 'Investimento adicionado com sucesso!';
            // Limpar formulário
            $_POST = [];
        } else {
            $error = 'Erro ao adicionar investimento. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Investimento - Carteira de Investimentos</title>
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
        
        .valor-calculado-card {
            background: var(--bg-input);
            border: 1px solid var(--border-light);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
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
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <div class="page-title">
                    <h1>💼 Adicionar Novo Investimento</h1>
                    <p class="text-secondary">Registre um novo investimento na sua carteira</p>
                </div>
                <a href="investments.php" class="btn btn-secondary">
                    ← Voltar para Investimentos
                </a>
            </div>

            <?php if($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 2rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    <?= htmlspecialchars($success) ?>
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
                            <div class="form-group">
                                <label for="nome" class="form-label">Nome do Investimento *</label>
                                <input type="text" 
                                       id="nome" 
                                       name="nome" 
                                       class="form-control" 
                                       placeholder="Ex: Banco do Brasil, Tesouro Selic, Bitcoin"
                                       value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="tipo_investimento" class="form-label">Tipo de Investimento *</label>
                                <select id="tipo_investimento" name="tipo_investimento" class="form-control form-select" required>
                                    <option value="">Selecione o tipo</option>
                                    <?php foreach($types as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= ($_POST['tipo_investimento'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ticker" class="form-label">Ticker/Código</label>
                                <input type="text" 
                                       id="ticker" 
                                       name="ticker" 
                                       class="form-control" 
                                       placeholder="Ex: BBAS3, SELIC, BTC"
                                       value="<?= htmlspecialchars($_POST['ticker'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="data_compra" class="form-label">Data da Compra *</label>
                                <input type="date" 
                                       id="data_compra" 
                                       name="data_compra" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($_POST['data_compra'] ?? date('Y-m-d')) ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="quantidade" class="form-label">Quantidade *</label>
                                <input type="number" 
                                       id="quantidade" 
                                       name="quantidade" 
                                       class="form-control" 
                                       step="0.000001"
                                       min="0"
                                       placeholder="0.000000"
                                       value="<?= htmlspecialchars($_POST['quantidade'] ?? '') ?>"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="preco_medio" class="form-label">Preço Médio (R$) *</label>
                                <input type="number" 
                                       id="preco_medio" 
                                       name="preco_medio" 
                                       class="form-control" 
                                       step="0.01"
                                       min="0"
                                       placeholder="0.00"
                                       value="<?= htmlspecialchars($_POST['preco_medio'] ?? '') ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="valor_atual" class="form-label">Valor Atual (R$)</label>
                            <input type="number" 
                                   id="valor_atual" 
                                   name="valor_atual" 
                                   class="form-control" 
                                   step="0.01"
                                   min="0"
                                   placeholder="Deixe vazio para calcular automaticamente"
                                   value="<?= htmlspecialchars($_POST['valor_atual'] ?? '') ?>">
                        </div>
                                                
                        <div class="form-group">
                            <label for="observacoes" class="form-label">Observações</label>
                            <textarea style="resize: none;"
                                      id="observacoes" 
                                      name="observacoes" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Observações adicionais sobre este investimento..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">💼 Adicionar Investimento</button>
                            <a href="investments.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Calcular valores em tempo real
        function calcularValores() {
            const quantidade = parseFloat(document.getElementById('quantidade').value) || 0;
            const precoMedio = parseFloat(document.getElementById('preco_medio').value) || 0;
            const valorAtualInput = parseFloat(document.getElementById('valor_atual').value) || 0;
            
            const valorInvestido = quantidade * precoMedio;
            const valorAtual = valorAtualInput > 0 ? valorAtualInput : valorInvestido;
            const rendimento = valorAtual - valorInvestido;
            const percentual = valorInvestido > 0 ? (rendimento / valorInvestido) * 100 : 0;
            
            document.getElementById('valor_investido').textContent = 
                'R$ ' + valorInvestido.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('valor_atual_calc').textContent = 
                'R$ ' + valorAtual.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            document.getElementById('rendimento').textContent = 
                'R$ ' + rendimento.toLocaleString('pt-BR', {minimumFractionDigits: 2}) + 
                ' (' + percentual.toFixed(2) + '%)';
            document.getElementById('rendimento').style.color = rendimento >= 0 ? '#00C851' : '#FF4444';
        }
        
        document.getElementById('quantidade').addEventListener('input', calcularValores);
        document.getElementById('preco_medio').addEventListener('input', calcularValores);
        document.getElementById('valor_atual').addEventListener('input', calcularValores);
        
        // Calcular na carga da página
        calcularValores();
    </script>
</body>
</html> 