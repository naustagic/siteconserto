<?php
/**
 * Configuração e Conexão com o Banco de Dados
 * Utiliza PDO para uma conexão segura.
 */

// --- Altere com suas credenciais do banco de dados ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'conserto'); // Nome do banco de dados que você criou
define('DB_USER', 'root'); // Seu usuário do MySQL
define('DB_PASS', ''); // Sua senha do MySQL
// ----------------------------------------------------

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lança exceções em caso de erro
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Retorna resultados como arrays associativos
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desabilita a emulação de prepared statements para segurança
];

try {
    // Cria a instância do PDO para a conexão
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Em caso de falha, exibe uma mensagem de erro genérica e encerra a execução
    // Em um ambiente de produção, é recomendado logar o erro em vez de exibi-lo.
    error_log("Erro de conexão com o banco de dados: " . $e->getMessage());
    die("Erro: Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}
?>