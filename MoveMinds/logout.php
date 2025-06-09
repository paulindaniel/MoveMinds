<?php
session_start(); // Inicia a sessão

// Apaga todas as variáveis da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona para a página de login
header("location: login.php");
exit;
?>
