<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
require_once '../config/database.php';

// --- LÓGICA DE ADIÇÃO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $icon_class = trim($_POST['icon_class']);
    $url = trim($_POST['url']);

    if (!empty($icon_class) && !empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
        $stmt = $pdo->prepare("INSERT INTO social_links (icon_class, url) VALUES (?, ?)");
        $stmt->execute([$icon_class, $url]);
        $_SESSION['success_message'] = "Link social adicionado com sucesso!";
    } else {
        $_SESSION['error_message'] = "Dados inválidos. Verifique o ícone e a URL.";
    }
}

// --- LÓGICA DE EXCLUSÃO ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM social_links WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success_message'] = "Link social removido com sucesso!";
}

header("Location: social_links_manager.php");
exit();