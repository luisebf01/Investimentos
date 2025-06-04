<?php
/**
 * PÁGINA PARA ADICIONAR NOVA META DE INVESTIMENTO
 * 
 * Esta página permite ao usuário criar novas metas financeiras
 * Funcionalidades:
 * - Formulário para criar meta
 * - Validação de dados
 * - Redirecionamento após sucesso
 */

require_once 'classes/Auth.php';
require_once 'classes/Investment.php';
require_once 'classes/AuditLog.php';

$auth = new Auth();
$investment = new Investment();
$auditLog = new AuditLog();

// Verificar autenticação
$auth->requireLogin();
$user = $auth->getCurrentUser();

$message = '';
$error = '';

// Processar envio do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $valor_meta = (float)str_replace(['.', ','], ['', '.'], $_POST['valor_meta'] ?? '0');
    $data_limite = !empty($_POST['data_limite']) ? $_POST['data_limite'] : null;
    $tipo_meta = $_POST['tipo_meta'] ?? 'valor_total';
    
    // Validação
    if (empty($titulo)) {
        $error = 'O título da meta é obrigatório';
    } elseif ($valor_meta <= 0) {
        $error = 'O valor da meta deve ser maior que zero';
    } else {
        try {
            require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();
            
            $query = "INSERT INTO metas_investimento (usuario_id, titulo, descricao, valor_meta, data_limite, tipo_meta) 
                     VALUES (:usuario_id, :titulo, :descricao, :valor_meta, :data_limite, :tipo_meta)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':usuario_id', $user['id']);
            $stmt->bindParam(':titulo', $titulo);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':valor_meta', $valor_meta);
            $stmt->bindParam(':data_limite', $data_limite);
            $stmt->bindParam(':tipo_meta', $tipo_meta);
            
            if ($stmt->execute()) {
                $meta_id = $conn->lastInsertId();
                
                // Registrar no log de auditoria
                $dados_meta = [
                    'titulo' => $titulo,
                    'descricao' => $descricao,
                    'valor_meta' => $valor_meta,
                    'data_limite' => $data_limite,
                    'tipo_meta' => $tipo_meta
                ];
                $auditLog->logMeta($user['id'], 'create', $meta_id, null, $dados_meta);
                
                header('Location: metas.php?success=Meta criada com sucesso!');
                exit;
            } else {
                $error = 'Erro ao criar meta. Tente novamente.';
            }
        } catch (Exception $e) {
            $error = 'Erro: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Meta - Carteira de Investimentos</title>
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
                <li><a href="investments.php">Investimentos</a></li>
                <li><a href="metas.php" class="active">Metas</a></li>
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
                    <h1>🎯 Adicionar Nova Meta</h1>
                    <p class="text-secondary">Defina seus objetivos financeiros</p>
                </div>
                <a href="metas.php" class="btn btn-secondary">
                    ← Voltar para Metas
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- FORMULÁRIO PRINCIPAL -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Dados da Meta</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="titulo" class="form-label">Título da Meta</label>
                                <input type="text" id="titulo" name="titulo" class="form-control" 
                                       placeholder="Ex: Primeira casa própria"
                                       value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="valor_meta" class="form-label">Valor da Meta (R$)</label>
                                <input type="text" id="valor_meta" name="valor_meta" class="form-control" 
                                       placeholder="Ex: 100.000,00"
                                       value="<?php echo htmlspecialchars($_POST['valor_meta'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="tipo_meta" class="form-label">Tipo de Meta</label>
                                <select id="tipo_meta" name="tipo_meta" class="form-control" required>
                                    <option value="">Selecione o tipo de meta</option>
                                    <option value="valor_total" <?php echo ($_POST['tipo_meta'] ?? 'valor_total') === 'valor_total' ? 'selected' : ''; ?>>
                                        Valor Total da Carteira
                                    </option>
                                    <option value="valor_mensal" <?php echo ($_POST['tipo_meta'] ?? '') === 'valor_mensal' ? 'selected' : ''; ?>>
                                        Aporte Mensal
                                    </option>
                                    <option value="percentual_rendimento" <?php echo ($_POST['tipo_meta'] ?? '') === 'percentual_rendimento' ? 'selected' : ''; ?>>
                                        Percentual de Rendimento
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="data_limite" class="form-label">Data Limite (Opcional)</label>
                                <input type="date" id="data_limite" name="data_limite" class="form-control"
                                       value="<?php echo htmlspecialchars($_POST['data_limite'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="descricao" class="form-label">Descrição</label>
                            <textarea style="resize: none;"
                                      id="descricao" 
                                      name="descricao" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Descreva o objetivo desta meta..."><?php echo htmlspecialchars($_POST['descricao'] ?? ''); ?></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">🎯 Criar Meta</button>
                            <a href="metas.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Máscara para valores monetários
        document.getElementById('valor_meta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = (parseInt(value) / 100).toFixed(2);
                value = value.replace('.', ',');
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                e.target.value = value;
            }
        });

        // Validação do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const titulo = document.getElementById('titulo').value.trim();
            const valorMeta = document.getElementById('valor_meta').value.trim();
            
            if (!titulo) {
                alert('Por favor, preencha o título da meta');
                e.preventDefault();
                return;
            }
            
            if (!valorMeta || parseFloat(valorMeta.replace(/\./g, '').replace(',', '.')) <= 0) {
                alert('Por favor, preencha um valor válido para a meta');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html> 