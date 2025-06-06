<?php
/**
 * BUSCAR TIPOS DE INVESTIMENTO VIA AJAX
 * 
 * Este arquivo retorna os tipos de investimento em formato JSON
 * para popular o select do modal
 */

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Incluir classes necessárias
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

try {
    // Verificar autenticação
    $auth = new Auth();
    $auth->requireLogin();

    // Criar instância da classe Investment
    $investment = new Investment();

    // Buscar tipos de investimento
    $types = $investment->getTypes();

    echo json_encode([
        'success' => true,
        'types' => $types
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar tipos de investimento: ' . $e->getMessage()
    ]);
}
?> 