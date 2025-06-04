<?php
/**
 * Script de Verificação de Segurança
 * Execute antes de fazer commit para garantir que nenhum dado sensível será versionado
 */

echo "🔒 VERIFICAÇÃO DE SEGURANÇA - Carteira de Investimentos\n";
echo "======================================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Verificar se database.php existe e não está sendo versionado
echo "📁 Verificando arquivos de configuração...\n";

if (file_exists('config/database.php')) {
    $success[] = "✅ config/database.php existe (configuração local presente)";
    
    // Verificar se contém credenciais padrão
    $dbContent = file_get_contents('config/database.php');
    if (strpos($dbContent, "password = ''") !== false && strpos($dbContent, "username = 'root'") !== false) {
        $warnings[] = "⚠️  Usando credenciais padrão (ok para desenvolvimento)";
    }
} else {
    $errors[] = "❌ config/database.php não encontrado! Copie de database.example.php";
}

if (file_exists('config/database.example.php')) {
    $success[] = "✅ config/database.example.php presente (template disponível)";
} else {
    $warnings[] = "⚠️  config/database.example.php não encontrado";
}

// Verificar .gitignore
echo "\n🚫 Verificando .gitignore...\n";

if (file_exists('.gitignore')) {
    $gitignoreContent = file_get_contents('.gitignore');
    
    $protectedFiles = [
        'config/database.php',
        '*.log',
        '.env',
        'backups/',
        'uploads/'
    ];
    
    foreach ($protectedFiles as $file) {
        if (strpos($gitignoreContent, $file) !== false) {
            $success[] = "✅ $file está protegido no .gitignore";
        } else {
            $errors[] = "❌ $file NÃO está protegido no .gitignore";
        }
    }
} else {
    $errors[] = "❌ .gitignore não encontrado!";
}

// Verificar se há arquivos sensíveis no diretório
echo "\n🔍 Procurando arquivos sensíveis no diretório...\n";

$sensitivePattterns = [
    '*.log' => 'Arquivos de log',
    '.env*' => 'Arquivos de ambiente',
    '*.sql' => 'Dumps de banco de dados',
    '*.backup' => 'Arquivos de backup',
    '*.key' => 'Chaves privadas',
    '*.pem' => 'Certificados'
];

foreach ($sensitivePattterns as $pattern => $description) {
    $files = glob($pattern);
    if (!empty($files)) {
        $warnings[] = "⚠️  Encontrados: " . implode(', ', $files) . " ($description)";
    }
}

// Verificar estrutura de diretórios sensíveis
$sensitiveDirs = ['logs', 'backups', 'uploads', 'cache', 'tmp'];
foreach ($sensitiveDirs as $dir) {
    if (is_dir($dir)) {
        $warnings[] = "⚠️  Diretório '$dir' existe - certifique-se que está no .gitignore";
    }
}

// Verificar permissões (apenas no Linux/Mac)
if (PHP_OS_FAMILY !== 'Windows') {
    echo "\n🔐 Verificando permissões...\n";
    
    if (file_exists('config/database.php')) {
        $perms = fileperms('config/database.php');
        $octal = sprintf('%o', $perms);
        
        if (substr($octal, -3) === '644' || substr($octal, -3) === '600') {
            $success[] = "✅ Permissões do database.php estão seguras";
        } else {
            $warnings[] = "⚠️  Permissões do database.php: $octal (recomendado: 600 ou 644)";
        }
    }
}

// Verificar se há dados de exemplo/teste
echo "\n📊 Verificando dados de teste...\n";

if (file_exists('sql/setup.sql')) {
    $sqlContent = file_get_contents('sql/setup.sql');
    if (strpos($sqlContent, 'admin@carteira.com') !== false) {
        $success[] = "✅ Usando dados de exemplo seguros";
    }
}

// Resumo final
echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 RESUMO DA VERIFICAÇÃO\n";
echo str_repeat("=", 50) . "\n";

if (!empty($errors)) {
    echo "\n❌ ERROS CRÍTICOS (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  AVISOS (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   $warning\n";
    }
}

if (!empty($success)) {
    echo "\n✅ VERIFICAÇÕES OK (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";

if (empty($errors)) {
    echo "🎉 PROJETO SEGURO PARA COMMIT!\n";
    echo "💡 Lembre-se de sempre verificar 'git status' antes de commit\n";
    exit(0);
} else {
    echo "🚨 CORRIJA OS ERROS ANTES DE FAZER COMMIT!\n";
    echo "📖 Consulte README_SECURITY.md para mais informações\n";
    exit(1);
}
?> 