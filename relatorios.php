<?php
/**
 * PÁGINA DE RELATÓRIOS E BACKUP
 * 
 * Funcionalidades:
 * - Gerar relatórios em PDF/Excel
 * - Backup completo dos dados
 * - Análise de performance
 * - Exportação para Imposto de Renda
 */

require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

$auth = new Auth();
$investment = new Investment();

// Verificar autenticação
$auth->requireLogin();
$user = $auth->getCurrentUser();

// Processar downloads/exports
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'backup_json':
            generateJSONBackup($user['id']);
            break;
        case 'export_csv':
            exportCSV($user['id']);
            break;
        case 'export_ir':
            exportIR($user['id']);
            break;
    }
}

// Buscar dados para relatórios
$summary = $investment->getSummaryByUser($user['id']);
$investments = $investment->getAllByUser($user['id']);
$summaryByType = $investment->getSummaryByType($user['id']);

// Estatísticas avançadas
$stats = calculateAdvancedStats($investments, $summary);

function calculateAdvancedStats($investments, $summary) {
    $stats = [
        'melhor_investimento' => null,
        'pior_investimento' => null,
        'media_rendimento' => 0,
        'volatilidade' => 0,
        'investimentos_positivos' => 0,
        'investimentos_negativos' => 0,
        'diversificacao_score' => 0
    ];
    
    if (empty($investments)) return $stats;
    
    // Encontrar melhor e pior investimento
    $melhor = $investments[0];
    $pior = $investments[0];
    
    $soma_rendimentos = 0;
    $positivos = 0;
    $negativos = 0;
    
    foreach ($investments as $inv) {
        if ($inv['percentual_rendimento'] > $melhor['percentual_rendimento']) {
            $melhor = $inv;
        }
        if ($inv['percentual_rendimento'] < $pior['percentual_rendimento']) {
            $pior = $inv;
        }
        
        $soma_rendimentos += $inv['percentual_rendimento'];
        
        if ($inv['rendimento'] > 0) $positivos++;
        if ($inv['rendimento'] < 0) $negativos++;
    }
    
    $stats['melhor_investimento'] = $melhor;
    $stats['pior_investimento'] = $pior;
    $stats['media_rendimento'] = $soma_rendimentos / count($investments);
    $stats['investimentos_positivos'] = $positivos;
    $stats['investimentos_negativos'] = $negativos;
    
    return $stats;
}

function generateJSONBackup($user_id) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Buscar todos os dados do usuário
        $backup_data = [
            'usuario' => [],
            'investimentos' => [],
            'metas' => [],
            'historico' => [],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Dados do usuário
        $query = "SELECT nome, email, data_criacao FROM usuarios WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $backup_data['usuario'] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Investimentos
        $query = "SELECT i.*, t.nome as tipo_nome FROM investimentos i 
                  JOIN tipos_investimento t ON i.tipo_id = t.id 
                  WHERE i.usuario_id = :id ORDER BY i.data_criacao";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $backup_data['investimentos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Metas (se a tabela existir)
        try {
            $query = "SELECT * FROM metas_investimento WHERE usuario_id = :id ORDER BY data_criacao";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $backup_data['metas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $backup_data['metas'] = [];
        }
        
        // Headers para download
        $filename = 'carteira_backup_' . date('Y-m-d_H-i-s') . '.json';
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
        
    } catch (Exception $e) {
        die('Erro ao gerar backup: ' . $e->getMessage());
    }
}

function exportCSV($user_id) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        $query = "SELECT i.*, t.nome as tipo_nome FROM investimentos i 
                  JOIN tipos_investimento t ON i.tipo_id = t.id 
                  WHERE i.usuario_id = :id ORDER BY i.data_criacao";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $filename = 'carteira_investimentos_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        // BOM para UTF-8
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Cabeçalho
        fputcsv($output, [
            'Nome', 'Ticker', 'Tipo', 'Quantidade', 'Preço Médio', 
            'Valor Investido', 'Valor Atual', 'Rendimento', 'Rendimento %', 
            'Data Compra', 'Data Criação'
        ], ';');
        
        // Dados
        foreach ($investments as $inv) {
            fputcsv($output, [
                $inv['nome'],
                $inv['ticker'],
                $inv['tipo_nome'],
                number_format($inv['quantidade'], 6, ',', '.'),
                number_format($inv['preco_medio'], 2, ',', '.'),
                number_format($inv['valor_investido'], 2, ',', '.'),
                number_format($inv['valor_atual'], 2, ',', '.'),
                number_format($inv['rendimento'], 2, ',', '.'),
                number_format($inv['percentual_rendimento'], 2, ',', '.') . '%',
                $inv['data_compra'] ? date('d/m/Y', strtotime($inv['data_compra'])) : '',
                date('d/m/Y H:i:s', strtotime($inv['data_criacao']))
            ], ';');
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        die('Erro ao exportar CSV: ' . $e->getMessage());
    }
}

function exportIR($user_id) {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        
        // Buscar apenas ações e FIIs para IR
        $query = "SELECT i.*, t.nome as tipo_nome FROM investimentos i 
                  JOIN tipos_investimento t ON i.tipo_id = t.id 
                  WHERE i.usuario_id = :id 
                  AND (t.nome IN ('Ações', 'FIIs'))
                  ORDER BY t.nome, i.nome";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $filename = 'declaracao_ir_' . date('Y') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        
        echo "\xEF\xBB\xBF";
        $output = fopen('php://output', 'w');
        
        // Cabeçalho específico para IR
        fputcsv($output, [
            'Tipo', 'Código', 'Nome da Empresa', 'Quantidade', 
            'Valor de Aquisição', 'Valor Atual', 'CNPJ', 'Observações'
        ], ';');
        
        foreach ($investments as $inv) {
            fputcsv($output, [
                $inv['tipo_nome'],
                $inv['ticker'],
                $inv['nome'],
                number_format($inv['quantidade'], 0, ',', '.'),
                number_format($inv['valor_investido'], 2, ',', '.'),
                number_format($inv['valor_atual'], 2, ',', '.'),
                '', // CNPJ - pode ser preenchido manualmente
                'Importado da Carteira de Investimentos'
            ], ';');
        }
        
        fclose($output);
        exit;
        
    } catch (Exception $e) {
        die('Erro ao exportar para IR: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios e Backup - Carteira de Investimentos</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .report-card {
            background: linear-gradient(135deg, #2a2a2a 0%, #1e1e1e 100%);
            border: 1px solid #444;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .report-card:hover {
            border-color: var(--primary-light);
            box-shadow: 0 4px 20px rgba(255, 215, 0, 0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: linear-gradient(135deg, #333 0%, #2a2a2a 100%);
            border: 1px solid #555;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .stat-title {
            font-size: 0.9rem;
            color: #ccc;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-light);
        }
        
        .export-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .export-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }
        
        .export-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
            text-decoration: none;
            color: #fff;
        }
        
        .performance-table {
            background: #2a2a2a;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 1rem;
        }
        
        .performance-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .performance-table th,
        .performance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        
        .performance-table th {
            background: #333;
            color: var(--primary-light);
            font-weight: 600;
        }
        
        .alert-info {
            background: rgba(33, 150, 243, 0.1);
            border: 1px solid #2196F3;
            color: #2196F3;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
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
                <li><a href="metas.php">Metas</a></li>
                <li><a href="calculadora.php">Calculadora</a></li>
                <li><a href="relatorios.php" class="active">Relatórios</a></li>
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
                    <h1>📋 Relatórios e Backup</h1>
                    <p style="color: #ccc;">Análises detalhadas e backup dos seus dados</p>
                </div>
            </div>

            <div class="alert-info">
                💡 <strong>Dica:</strong> Faça backup regular dos seus dados e exporte relatórios para acompanhar sua evolução financeira.
            </div>

            <!-- Estatísticas Avançadas -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📈 Análise de Performance</h2>
                </div>
                <div class="card-body">
                
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-title">Total Investido</div>
                        <div class="stat-value">R$ <?php echo number_format($summary['total_investido'] ?? 0, 2, ',', '.'); ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-title">Valor Atual</div>
                        <div class="stat-value">R$ <?php echo number_format($summary['total_atual'] ?? 0, 2, ',', '.'); ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-title">Rendimento Total</div>
                        <div class="stat-value <?php echo ($summary['total_rendimento'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                            R$ <?php echo number_format($summary['total_rendimento'] ?? 0, 2, ',', '.'); ?>
                        </div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-title">Rentabilidade</div>
                        <div class="stat-value <?php echo ($summary['total_rendimento'] ?? 0) >= 0 ? 'positive' : 'negative'; ?>">
                            <?php 
                            $rentabilidade = ($summary['total_investido'] ?? 0) > 0 ? 
                                (($summary['total_rendimento'] ?? 0) / $summary['total_investido']) * 100 : 0;
                            echo ($rentabilidade >= 0 ? '+' : '') . number_format($rentabilidade, 2, ',', '.'); 
                            ?>%
                        </div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-title">Investimentos Positivos</div>
                        <div class="stat-value"><?php echo $stats['investimentos_positivos']; ?></div>
                    </div>
                    
                    <div class="stat-box">
                        <div class="stat-title">Investimentos Negativos</div>
                        <div class="stat-value"><?php echo $stats['investimentos_negativos']; ?></div>
                    </div>
                </div>

                <?php if ($stats['melhor_investimento'] && $stats['pior_investimento']): ?>
                <div class="performance-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Investimento</th>
                                <th>Rendimento</th>
                                <th>Percentual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>🏆 Melhor</td>
                                <td><?php echo htmlspecialchars($stats['melhor_investimento']['nome']); ?></td>
                                <td class="positive">R$ <?php echo number_format($stats['melhor_investimento']['rendimento'], 2, ',', '.'); ?></td>
                                <td class="positive">+<?php echo number_format($stats['melhor_investimento']['percentual_rendimento'], 2, ',', '.'); ?>%</td>
                            </tr>
                            <tr>
                                <td>📉 Pior</td>
                                <td><?php echo htmlspecialchars($stats['pior_investimento']['nome']); ?></td>
                                <td class="negative">R$ <?php echo number_format($stats['pior_investimento']['rendimento'], 2, ',', '.'); ?></td>
                                <td class="negative"><?php echo number_format($stats['pior_investimento']['percentual_rendimento'], 2, ',', '.'); ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                </div>
            </div>            

            <!-- Backup e Exportação -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">💾 Backup e Exportação</h2>
                </div>
                <div class="card-body">
                    <p style="color: #ccc; margin-bottom: 1.5rem;">
                        Mantenha seus dados seguros e exporte relatórios para análise externa ou declaração de IR.
                    </p>
                
                <div class="export-buttons">
                    <a href="?action=backup_json" class="export-btn">
                        📋 Backup Completo (JSON)
                    </a>
                    
                    <a href="?action=export_csv" class="export-btn">
                        📊 Exportar Excel/CSV
                    </a>
                    
                    <a href="?action=export_ir" class="export-btn">
                        🧾 Relatório para IR
                    </a>
                    
                    <button onclick="window.print()" class="export-btn">
                        🖨️ Imprimir Relatório
                    </button>
                </div>
                </div>
            </div>

            <!-- Instruções -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">ℹ️ Instruções</h2>
                </div>
                <div class="card-body">                
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <div>
                            <h3 style="color: #fff; margin-bottom: 1rem;">📋 Backup Completo</h3>
                            <ul style="color: #ccc; line-height: 1.6; padding-left: 1.25rem;">
                                <li>Salva todos os seus dados em formato JSON</li>
                                <li>Inclui investimentos, metas e configurações</li>
                                <li>Recomendado fazer backup mensal</li>
                                <li>Pode ser usado para restaurar dados</li>
                            </ul>
                        </div>
                        
                        <div>
                            <h3 style="color: #fff; margin-bottom: 1rem;">🧾 Relatório para IR</h3>
                            <ul style="color: #ccc; line-height: 1.6; padding-left: 1.25rem;">
                                <li>Exporta apenas ações e FIIs</li>
                                <li>Formato compatível com declaração</li>
                                <li>Inclui códigos e valores atuais</li>
                                <li>Facilita preenchimento do IRPF</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Animações de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.report-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.animation = 'fadeInUp 0.6s ease forwards';
                }, index * 200);
            });
        });

        // Confirmação para backup
        document.querySelector('a[href*="backup_json"]').addEventListener('click', function(e) {
            if (!confirm('Deseja fazer o backup completo dos seus dados?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html> 