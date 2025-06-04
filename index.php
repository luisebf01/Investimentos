<?php
require_once 'classes/Auth.php';

$auth = new Auth();

// Redirecionar baseado no status de login
if($auth->isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?> 