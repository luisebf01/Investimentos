<?php
/**
 * API ENDPOINT - ATUALIZAR COTAÇÕES
 * 
 * Este arquivo é chamado via AJAX para atualizar cotações dos investimentos
 * Resposta em JSON com resultado da operação
 */

// Desativar exibição de erros para não interferir no JSON
error_reporting(0);
ini_set('display_errors', 0);

// Headers para API JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Iniciar sessão para verificar login
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário está logado (método simples)
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Usuário não autenticado'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Verificar se temos configuração de banco
if (!file_exists('../config/database.php')) {
    echo json_encode([
        'success' => false,
        'error' => 'Configuração de banco não encontrada'
    ]);
    exit;
}

// Incluir configuração de banco
require_once '../config/database.php';

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Criar conexão
    $database = new Database();
    $conn = $database->getConnection();

    // Buscar investimentos com ticker do usuário
    $query = "SELECT id, nome, ticker, tipo_id, quantidade, preco_medio, valor_investido 
              FROM investimentos 
              WHERE usuario_id = :user_id 
              AND ticker IS NOT NULL 
              AND ticker != ''";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $investments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [
        'updated' => 0,
        'errors' => 0,
        'messages' => []
    ];

    if (empty($investments)) {
        $results['messages'][] = 'Nenhum investimento com ticker encontrado';
    } else {
        // Simular atualização bem-sucedida
        $results['updated'] = count($investments);
        $results['messages'][] = 'Simulação: ' . count($investments) . ' investimentos processados';
        
        // Simular atualização de alguns preços
        foreach ($investments as $inv) {
            // Simular variação de -2% a +3%
            $variacao = (rand(-200, 300) / 100); // -2.00 a +3.00
            $novo_valor = $inv['valor_investido'] * (1 + ($variacao / 100));
            
            // Atualizar valor no banco
            $update_query = "UPDATE investimentos SET 
                            valor_atual = :valor_atual,
                            rendimento = :rendimento,
                            percentual_rendimento = :percentual_rendimento
                            WHERE id = :id";
            
            $rendimento = $novo_valor - $inv['valor_investido'];
            $percentual = $inv['valor_investido'] > 0 ? ($rendimento / $inv['valor_investido']) * 100 : 0;
            
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bindParam(':valor_atual', $novo_valor);
            $update_stmt->bindParam(':rendimento', $rendimento);
            $update_stmt->bindParam(':percentual_rendimento', $percentual);
            $update_stmt->bindParam(':id', $inv['id']);
            $update_stmt->execute();
            
            $results['messages'][] = $inv['nome'] . ': ' . ($variacao >= 0 ? '+' : '') . number_format($variacao, 2) . '%';
        }
    }
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'data' => $results,
        'message' => 'Cotações simuladas com sucesso',
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    // Resposta de erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?> 