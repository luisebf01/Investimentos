<?php
/**
 * API ENDPOINT DEBUG - ATUALIZAR COTAÇÕES
 * Versão com logs detalhados para identificar problemas
 */

// Ativar logs de erro
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Headers para API JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

$debug_info = [];
$debug_info['step'] = 'inicio';

try {
    $debug_info['step'] = 'verificando_metodo';
    
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $debug_info['error'] = 'Método não é POST: ' . $_SERVER['REQUEST_METHOD'];
        echo json_encode(['success' => false, 'debug' => $debug_info]);
        exit;
    }

    $debug_info['step'] = 'incluindo_auth';
    
    // Tentar incluir Auth
    if (!file_exists('../classes/Auth.php')) {
        $debug_info['error'] = 'Arquivo Auth.php não encontrado';
        echo json_encode(['success' => false, 'debug' => $debug_info]);
        exit;
    }
    
    require_once '../classes/Auth.php';
    $debug_info['auth_included'] = true;

    $debug_info['step'] = 'verificando_autenticacao';
    
    // Verificar autenticação
    $auth = new Auth();
    if (!$auth->isLoggedIn()) {
        $debug_info['error'] = 'Usuário não está logado';
        echo json_encode(['success' => false, 'debug' => $debug_info]);
        exit;
    }

    $debug_info['step'] = 'obtendo_usuario';
    $user = $auth->getCurrentUser();
    $debug_info['user_id'] = $user['id'] ?? 'não definido';

    $debug_info['step'] = 'verificando_price_updater';
    
    // Verificar se PriceUpdater existe
    if (!file_exists('../classes/PriceUpdater.php')) {
        $debug_info['error'] = 'Arquivo PriceUpdater.php não encontrado';
        echo json_encode(['success' => false, 'debug' => $debug_info]);
        exit;
    }
    
    require_once '../classes/PriceUpdater.php';
    $debug_info['price_updater_included'] = true;

    $debug_info['step'] = 'criando_instancia';
    
    // Verificar se a classe existe
    if (!class_exists('PriceUpdater')) {
        $debug_info['error'] = 'Classe PriceUpdater não existe';
        echo json_encode(['success' => false, 'debug' => $debug_info]);
        exit;
    }

    $priceUpdater = new PriceUpdater();
    $debug_info['instance_created'] = true;

    $debug_info['step'] = 'executando_update';
    
    // Simular atualização por enquanto
    $results = [
        'updated' => 0,
        'errors' => 0,
        'messages' => ['Simulação de atualização - debug mode']
    ];
    
    $debug_info['step'] = 'sucesso';
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'data' => $results,
        'message' => 'Debug executado com sucesso',
        'debug' => $debug_info,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    $debug_info['exception'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ];
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'debug' => $debug_info,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} catch (Error $e) {
    $debug_info['fatal_error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ];
    
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'debug' => $debug_info,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?> 