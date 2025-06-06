<?php
/**
 * PROCESSAR ADIÇÃO DE INVESTIMENTO VIA AJAX
 * 
 * Este arquivo processa o formulário de adicionar investimento
 * e retorna uma resposta JSON para o modal
 */

// Configurar headers para JSON
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Incluir classes necessárias
require_once 'classes/Auth.php';
require_once 'classes/Investment.php';

try {
    // Verificar se é uma requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }

    // Verificar autenticação
    $auth = new Auth();
    $auth->requireLogin();
    $user = $auth->getCurrentUser();

    // Criar instância da classe Investment
    $investment = new Investment();

    // Validar e processar dados do formulário
    $data = [
        'usuario_id' => $user['id'],
        'tipo_id' => $_POST['tipo_id'] ?? '',
        'nome' => trim($_POST['nome'] ?? ''),
        'ticker' => trim($_POST['ticker'] ?? ''),
        'quantidade' => floatval($_POST['quantidade'] ?? 0),
        'preco_medio' => floatval($_POST['preco_medio'] ?? 0),
        'valor_atual' => floatval($_POST['valor_atual'] ?? 0),
        'data_compra' => $_POST['data_compra'] ?? '',
        'observacoes' => trim($_POST['observacoes'] ?? '')
    ];

    // Validações
    $errors = [];

    if (empty($data['tipo_id'])) {
        $errors[] = 'Tipo de investimento é obrigatório';
    }

    if (empty($data['nome'])) {
        $errors[] = 'Nome do investimento é obrigatório';
    }

    if ($data['quantidade'] <= 0) {
        $errors[] = 'Quantidade deve ser maior que zero';
    }

    if ($data['preco_medio'] <= 0) {
        $errors[] = 'Preço médio deve ser maior que zero';
    }

    if (empty($data['data_compra'])) {
        $errors[] = 'Data de compra é obrigatória';
    }

    // Se há erros, retornar lista de erros
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro de validação',
            'errors' => $errors
        ]);
        exit;
    }

    // Se valor atual não foi informado, calcular automaticamente
    if ($data['valor_atual'] <= 0) {
        $data['valor_atual'] = $data['quantidade'] * $data['preco_medio'];
    }

    // Tentar criar o investimento
    $id = $investment->create($data);

    if ($id) {
        // Buscar o resumo atualizado para retornar
        $summary = $investment->getSummaryByUser($user['id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Investimento adicionado com sucesso!',
            'investment_id' => $id,
            'summary' => $summary
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao adicionar investimento. Tente novamente.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno: ' . $e->getMessage()
    ]);
}
?> 