<?php
// Inicia a sessão para poder usar variáveis de sessão (ex: para mensagens de erro)
session_start();

// Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';
require_once '../includes/functions.php';

$cor_primaria = get_config($pdo, 'cor_primaria', '#3b82f6');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Painel Administrativo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --cor-primaria: <?php echo sanitize_output($cor_primaria); ?>; }
        body { font-family: 'Inter', sans-serif; }
        .brand-bg { background-color: var(--cor-primaria); }
        .brand-text { color: var(--cor-primaria); }
        .brand-border-focus:focus { border-color: var(--cor-primaria); box-shadow: 0 0 0 2px var(--cor-primaria); }
        .brand-bg-hover:hover { filter: brightness(1.1); }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold text-center brand-text mb-6">ReparoPRO - Acesso Restrito</h1>

        <?php
        // Exibe mensagem de erro, se houver
        if (isset($_SESSION['login_error'])) {
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
            echo '<span class="block sm:inline">' . sanitize_output($_SESSION['login_error']) . '</span>';
            echo '</div>';
            // Limpa a mensagem de erro da sessão para não exibir novamente
            unset($_SESSION['login_error']);
        }
        ?>

        <form action="auth.php" method="POST">
            <div class="mb-4">
                <label for="login" class="block text-gray-700 text-sm font-bold mb-2">Login (E-mail):</label>
                <input type="email" id="login" name="login" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline brand-border-focus" placeholder="admin@reparopro.com">
            </div>
            <div class="mb-6">
                <label for="senha" class="block text-gray-700 text-sm font-bold mb-2">Senha:</label>
                <input type="password" id="senha" name="senha" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline brand-border-focus" placeholder="************">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="w-full brand-bg text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline brand-bg-hover transition-all">
                    Entrar
                </button>
            </div>
        </form>
         <div class="text-center mt-4">
            <a href="../index.php" class="text-sm text-gray-600 hover:text-[var(--cor-primaria)]">&larr; Voltar para o site</a>
        </div>
    </div>

</body>
</html>