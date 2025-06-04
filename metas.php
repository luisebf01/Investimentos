<?php
/**
 * PÁGINA DE METAS DE INVESTIMENTO
 * 
 * Funcionalidades:
 * - Visualizar metas ativas
 * - Criar novas metas
 * - Acompanhar progresso
 * - Editar/excluir metas
 */

require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

$auth = new Auth();
$investment = new Investment();

// Verificar autenticação
$auth->requireLogin();
$user = $auth->getCurrentUser();

// Processar mensagens da URL
$message = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Buscar dados atuais da carteira
$summary = $investment->getSummaryByUser($user['id']);
$total_atual = $summary['total_atual'] ?? 0;

// Buscar metas do usuário
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM metas_investimento 
              WHERE usuario_id = :usuario_id 
              ORDER BY status ASC, data_criacao DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $user['id']);
    $stmt->execute();
    $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular valores dinâmicos das metas (sem alterar banco)
    foreach ($metas as &$meta) {
        if ($meta['tipo_meta'] === 'valor_total') {
            // Para metas de valor total, usar o valor atual da carteira
            $meta['valor_atual_calculado'] = $total_atual;
            $meta['progresso'] = $meta['valor_meta'] > 0 ? ($total_atual / $meta['valor_meta']) * 100 : 0;
        } else {
            // Para outros tipos de meta, usar o valor salvo no banco
            $meta['valor_atual_calculado'] = $meta['valor_atual'] ?? 0;
            $meta['progresso'] = $meta['valor_meta'] > 0 ? ($meta['valor_atual_calculado'] / $meta['valor_meta']) * 100 : 0;
        }
    }
    unset($meta); // CRUCIAL: Remove a referência para evitar problemas
    
} catch (Exception $e) {
    $metas = [];
    $error = 'Erro ao buscar metas: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas de Investimento - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .meta-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
            border: 1px solid #444;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .meta-card:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.1);
        }
        
        .meta-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .meta-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-light);
            margin: 0;
        }
        
        .meta-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-ativo { background: #00C851; color: white; }
        .status-concluido { background: #2196F3; color: white; }
        .status-pausado { background: #FF8800; color: white; }
        
        .progress-container {
            background: #333;
            border-radius: 10px;
            height: 8px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #2196F3 0%, #1976D2 100%);
            transition: width 0.5s ease;
            border-radius: 10px;
        }
        
        .meta-values {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .value-item {
            text-align: center;
        }
        
        .value-label {
            font-size: 0.9rem;
            color: #ccc;
            margin-bottom: 0.25rem;
        }
        
        .value-amount {
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
        }
        
        .achievement-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: #fff;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .meta-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .info-icon {
            font-size: 1.2rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            flex-shrink: 0;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #999;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        .info-value {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid;
        }
        
        .alert-success {
            background-color: rgba(0, 200, 81, 0.1);
            border-color: #00C851;
            color: #00C851;
        }
        
        .alert-danger {
            background-color: rgba(255, 68, 68, 0.1);
            border-color: #FF4444;
            color: #FF4444;
        }
        
        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #545b62 100%);
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(135deg, #c82333 0%, #bd2130 100%);
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .meta-info-grid {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
            
            .info-item {
                gap: 0.5rem;
            }
            
            .info-icon {
                width: 1.5rem;
                height: 1.5rem;
                font-size: 1rem;
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
                    <h1>🎯 Metas de Investimento</h1>
                    <p style="color: #ccc;">Defina e acompanhe seus objetivos financeiros</p>
                </div>
                <a href="add_meta.php" class="btn btn-primary">
                    + Adicionar Meta
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Lista de Metas -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📊 Suas Metas</h2>
                </div>
                <div class="card-body">
                
                <?php if (empty($metas)): ?>
                    <div class="meta-card" style="text-align: center; padding: 3rem;">
                        <h3 style="color: #ccc;">🎯 Nenhuma meta definida</h3>
                        <p style="color: #999;">Crie sua primeira meta para começar a acompanhar seus objetivos financeiros.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($metas as $meta): ?>
                        <div class="meta-card">
                            <div class="meta-header">
                                <h3 class="meta-title"><?php echo htmlspecialchars($meta['titulo']); ?></h3>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="meta-status status-<?php echo $meta['status']; ?>">
                                        <?php echo ucfirst($meta['status']); ?>
                                    </span>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="edit_meta.php?id=<?php echo $meta['id']; ?>" class="btn btn-sm btn-secondary" title="Editar meta">
                                            ✏️
                                        </a>
                                        <a href="delete_meta.php?id=<?php echo $meta['id']; ?>" class="btn btn-sm btn-danger" title="Excluir meta">
                                            🗑️
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($meta['descricao']): ?>
                                <p style="color: #ccc; margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars($meta['descricao']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Informações Adicionais -->
                            <div class="meta-info-grid">
                                <div class="info-item">
                                    <span class="info-icon">🎯</span>
                                    <div class="info-content">
                                        <div class="info-label">Tipo de Meta</div>
                                        <div class="info-value">
                                            <?php 
                                            switch($meta['tipo_meta']) {
                                                case 'valor_total': echo 'Valor Total da Carteira'; break;
                                                case 'valor_mensal': echo 'Aporte Mensal'; break;
                                                case 'percentual_rendimento': echo 'Percentual de Rendimento'; break;
                                                default: echo ucfirst(str_replace('_', ' ', $meta['tipo_meta']));
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-icon">📅</span>
                                    <div class="info-content">
                                        <div class="info-label">Data de Criação</div>
                                        <div class="info-value"><?php echo date('d/m/Y', strtotime($meta['data_criacao'])); ?></div>
                                    </div>
                                </div>
                                
                                <?php if ($meta['data_limite']): ?>
                                <div class="info-item">
                                    <span class="info-icon">⏰</span>
                                    <div class="info-content">
                                        <div class="info-label">Data Limite</div>
                                        <div class="info-value"><?php echo date('d/m/Y', strtotime($meta['data_limite'])); ?></div>
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="info-item">
                                    <span class="info-icon">∞</span>
                                    <div class="info-content">
                                        <div class="info-label">Data Limite</div>
                                        <div class="info-value">Sem prazo definido</div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="progress-container">
                                <div class="progress-bar" style="width: <?php echo min(100, $meta['progresso']); ?>%"></div>
                            </div>
                            
                            <div class="meta-values">
                                <div class="value-item">
                                    <div class="value-label">Valor Atual</div>
                                    <div class="value-amount">
                                        R$ <?php echo number_format($meta['valor_atual_calculado'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                                
                                <div class="value-item">
                                    <div class="value-label">Meta</div>
                                    <div class="value-amount">
                                        R$ <?php echo number_format($meta['valor_meta'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                                
                                <div class="value-item">
                                    <div class="value-label">Progresso</div>
                                    <div class="value-amount">
                                        <?php echo number_format($meta['progresso'], 1, ',', '.'); ?>%
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($meta['progresso'] >= 100): ?>
                                <div class="achievement-badge">
                                    🎉 Meta Alcançada! Parabéns!
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animação das barras de progresso
        document.addEventListener('DOMContentLoaded', function() {
            const progressBars = document.querySelectorAll('.progress-bar');
            progressBars.forEach((bar, index) => {
                setTimeout(() => {
                    bar.style.width = bar.style.width; // Trigger animation
                }, index * 200);
            });
        });
    </script>
</body>
</html> 