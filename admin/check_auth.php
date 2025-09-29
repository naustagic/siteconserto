<?php
// Inicia a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se a variável de sessão 'user_id' não está definida
if (!isset($_SESSION['user_id'])) {
    // Se não estiver, o usuário não está logado.
    // Armazena uma mensagem de erro para exibir na página de login
    $_SESSION['login_error'] = 'Você precisa fazer login para acessar esta página.';
    
    // Redireciona para a página de login
    header('Location: login.php');
    
    // Encerra a execução do script para garantir que nada mais seja processado
    exit();
}
?>