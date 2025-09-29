<?php
// Sempre inicie a sessão em scripts que a manipulam
session_start();

// Inclui a conexão com o banco de dados
require_once '../config/database.php';

// Verifica se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Se não for, redireciona para a página de login e encerra o script
    header('Location: login.php');
    exit();
}

// Pega o login e a senha do formulário
$login = $_POST['login'];
$senha = $_POST['senha'];

// Validação básica
if (empty($login) || empty($senha)) {
    $_SESSION['login_error'] = 'Por favor, preencha todos os campos.';
    header('Location: login.php');
    exit();
}

try {
    // Prepara a consulta para buscar o usuário pelo login (prevenção de SQL Injection)
    $stmt = $pdo->prepare("SELECT id, nome, senha, nivel_acesso FROM usuarios WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    // Verifica se o usuário foi encontrado e se a senha está correta
    if ($user && password_verify($senha, $user['senha'])) {
        // Senha correta, login bem-sucedido!
        
        // Regenera o ID da sessão para prevenir ataques de session fixation
        session_regenerate_id(true);

        // Armazena informações do usuário na sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['user_level'] = $user['nivel_acesso'];

        // Redireciona para o painel principal
        header('Location: index.php');
        exit();
    } else {
        // Usuário não encontrado ou senha incorreta
        $_SESSION['login_error'] = 'Login ou senha inválidos.';
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    // Em caso de erro no banco de dados
    error_log("Erro de autenticação: " . $e->getMessage());
    $_SESSION['login_error'] = 'Ocorreu um erro no servidor. Tente novamente.';
    header('Location: login.php');
    exit();
}
?>