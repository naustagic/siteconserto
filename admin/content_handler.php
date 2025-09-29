<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') {
    die("Acesso negado.");
}
require_once '../config/database.php';
require_once '../includes/functions.php';

// Função para atualizar ou inserir uma configuração
function update_config($pdo, $key, $value) {
    $sql = "INSERT INTO config_site (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$key, $value, $value]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Salva o conteúdo da página "Guia de Envio"
    if(isset($_POST['guia_envio_conteudo'])) {
        // O conteúdo do TinyMCE pode conter HTML, então não usamos sanitize_output aqui.
        // A segurança é garantida pois apenas o Admin pode editar.
        update_config($pdo, 'guia_envio_conteudo', $_POST['guia_envio_conteudo']);
    }

    // Salva as chaves de integração
    if(isset($_POST['telegram_token'])) {
        update_config($pdo, 'telegram_token', sanitize_output($_POST['telegram_token']));
    }
    if(isset($_POST['telegram_chat_id'])) {
        update_config($pdo, 'telegram_chat_id', sanitize_output($_POST['telegram_chat_id']));
    }
    if(isset($_POST['facebook_pixel_id'])) {
        update_config($pdo, 'facebook_pixel_id', sanitize_output($_POST['facebook_pixel_id']));
    }

    $_SESSION['success_message'] = 'Conteúdo e Integrações salvos com sucesso!';
    header('Location: settings.php?tab=conteudo'); // Redireciona de volta para a aba correta
    exit();
}
?>