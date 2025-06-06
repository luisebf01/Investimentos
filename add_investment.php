<?php
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

$auth = new Auth();
$auth->requireLogin();

$user = $auth->getCurrentUser();
$investment = new Investment();
$types = $investment->getTypes();

$error = '';
$success = '';

// Processar formulário
if($_POST) {
    $data = [
        'usuario_id' => $user['id'],
        'tipo_id' => $_POST['tipo_id'] ?? '',
        'nome' => $_POST['nome'] ?? '',
        'ticker' => $_POST['ticker'] ?? '',
        'quantidade' => floatval($_POST['quantidade'] ?? 0),
        'preco_medio' => floatval($_POST['preco_medio'] ?? 0),
        'valor_atual' => floatval($_POST['valor_atual'] ?? 0),
        'data_compra' => $_POST['data_compra'] ?? '',
        'observacoes' => $_POST['observacoes'] ?? ''
    ];
    
    if(empty($data['tipo_id']) || empty($data['nome']) || $data['quantidade'] <= 0 || $data['preco_medio'] <= 0) {
        $error = 'Por favor, preencha todos os campos obrigatórios.';
    } else {
        $id = $investment->create($data);
        if($id) {
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
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="logo">💰 Carteira</div>
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="add_investment.php" class="active">Adicionar</a></li>
                <li><a href="faturamento.php">Faturamento</a></li>
                <li><a href="balanco.php">Balanço</a></li>
                <li><a href="estatisticas.php">Estatísticas</a></li>
                <li><a href="profile.php">Perfil</a></li>
                <li><a href="logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

    <div class="container" style="padding-top: 2rem;">
        <div class="fade-in">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">Adicionar Novo Investimento</h1>
                </div>
                
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <div class="form-group">
                                <label for="tipo_id" class="form-label">Tipo de Investimento *</label>
                                <select id="tipo_id" name="tipo_id" class="form-control form-select" required>
                                    <option value="">Selecione o tipo</option>
                                    <?php foreach($types as $type): ?>
                                    <option value="<?= $type['id'] ?>" <?= ($_POST['tipo_id'] ?? '') == $type['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['nome']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
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
                                <label for="ticker" class="form-label">Ticker/Código</label>
                                <input type="text" 
                                       id="ticker" 
                                       name="ticker" 
                                       class="form-control" 
                                       placeholder="Ex: BBAS3, SELIC, BTC"
                                       value="<?= htmlspecialchars($_POST['ticker'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="data_compra" class="form-label">Data da Compra</label>
                                <input type="date" 
                                       id="data_compra" 
                                       name="data_compra" 
                                       class="form-control"
                                       value="<?= htmlspecialchars($_POST['data_compra'] ?? date('Y-m-d')) ?>">
                            </div>
                        </div>
                        
                        <div>
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
                                <small style="color: #888;">Se não informado, será calculado como quantidade × preço médio</small>
                            </div>
                            
                            <div id="valor_calculado" style="padding: 1rem; background: rgba(40, 40, 40, 0.5); border-radius: 8px; margin-top: 1rem;">
                                <div><strong>Valor Investido:</strong> <span id="valor_investido">R$ 0,00</span></div>
                                <div><strong>Valor Atual:</strong> <span id="valor_atual_calc">R$ 0,00</span></div>
                                <div><strong>Rendimento:</strong> <span id="rendimento">R$ 0,00 (0%)</span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacoes" class="form-label">Observações</label>
                        <textarea id="observacoes" 
                                  name="observacoes" 
                                  class="form-control" 
                                  rows="3"
                                  placeholder="Observações sobre este investimento..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">Adicionar Investimento</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
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