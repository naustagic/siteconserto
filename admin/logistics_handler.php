<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? null;

// Textos padrão
$default_title = 'Seu Dispositivo em Mãos de Especialistas';
$default_subtitle = 'Reparos rápidos e com garantia para Consoles, PCs, Notebooks, Celulares e mais.';

// Adicionar novo(s) banner(s)
if ($action === 'add_banner' && isset($_FILES['banner_image'])) {
    // Se o título ou subtítulo vierem em branco, usa o padrão.
    $title = !empty(trim($_POST['title'])) ? trim($_POST['title']) : $default_title;
    $subtitle = !empty(trim($_POST['subtitle'])) ? trim($_POST['subtitle']) : $default_subtitle;
    
    $files = $_FILES['banner_image'];
    
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        if ($files['error'][$key] === UPLOAD_ERR_OK) {
            $file_to_process = [
                'name' => $files['name'][$key], 'type' => $files['type'][$key],
                'tmp_name' => $tmp_name, 'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];
            $upload_dir = '../assets/uploads/banners/';
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
            $filename = 'banner_' . uniqid() . '.jpg';
            $destination = $upload_dir . $filename;

            if (process_uploaded_image($file_to_process, $destination, 1920, 1080)) {
                $db_path = 'assets/uploads/banners/' . $filename;
                $stmt = $pdo->prepare("INSERT INTO hero_banners (image_path, title, subtitle) VALUES (?, ?, ?)");
                $stmt->execute([$db_path, $title, $subtitle]);
            }
        }
    }
}

// --- NOVA LÓGICA DE EDIÇÃO ---
if ($action === 'edit_banner' && isset($_POST['banner_id'])) {
    $id = $_POST['banner_id'];
    // Se o título ou subtítulo vierem em branco, usa o padrão.
    $title = !empty(trim($_POST['title'])) ? trim($_POST['title']) : $default_title;
    $subtitle = !empty(trim($_POST['subtitle'])) ? trim($_POST['subtitle']) : $default_subtitle;

    $stmt = $pdo->prepare("UPDATE hero_banners SET title = ?, subtitle = ? WHERE id = ?");
    $stmt->execute([$title, $subtitle, $id]);
}


// Excluir um banner
if ($action === 'delete_banner' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT image_path FROM hero_banners WHERE id = ?");
    $stmt->execute([$id]);
    $path = $stmt->fetchColumn();
    if ($path && file_exists('../' . $path)) {
        unlink('../' . $path);
    }
    $stmt_delete = $pdo->prepare("DELETE FROM hero_banners WHERE id = ?");
    $stmt_delete->execute([$id]);
}

$_SESSION['success_message'] = 'Ação executada com sucesso!';
header('Location: settings.php?tab=banners');
exit;
?>