<?php
// Teste simples da API
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

echo json_encode([
    'success' => true,
    'message' => 'API funcionando',
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 