<?php
require_once 'classes/Auth.php';
require_once 'classes/AuditLog.php';

session_start();

$auth = new Auth();
$auditLog = new AuditLog();

// Registrar logout se usuário estiver logado
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $session_id = session_id();
    $auditLog->registrarLogout($user_id, $session_id);
}

$auth->logout();

header('Location: login.php');
exit();
?> 