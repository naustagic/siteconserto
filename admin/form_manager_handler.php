<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') {
    die("Acesso negado.");
}
require_once '../config/database.php';

$action = $_REQUEST['action'] ?? null;

// Ação de Adicionar
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $name = $_POST['name'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $requires_brand_model = isset($_POST['requires_brand_model']) ? 1 : 0;
    
    $icon_path = null;
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/img/icons/';
        $extension = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));
        $filename = 'icon_' . uniqid() . '.' . $extension;
        if(move_uploaded_file($_FILES['icon']['tmp_name'], $upload_dir . $filename)) {
            $icon_path = 'assets/img/icons/' . $filename;
        }
    }
    
    $sql = "INSERT INTO form_options (parent_id, type, name, icon_path, requires_brand_model) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parent_id, $type, $name, $icon_path, $requires_brand_model]);
}

// Ação de Editar
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $parent_id = !empty($_POST['parent_id']) ? $_POST['parent_id'] : null;
    $requires_brand_model = isset($_POST['requires_brand_model']) ? 1 : 0;

    // Apenas atualiza o ícone se um novo for enviado
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
         $upload_dir = '../assets/img/icons/';
        $extension = strtolower(pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION));
        $filename = 'icon_' . uniqid() . '.' . $extension;
        if(move_uploaded_file($_FILES['icon']['tmp_name'], $upload_dir . $filename)) {
            $icon_path = 'assets/img/icons/' . $filename;
            // Atualiza o caminho do ícone no banco
            $stmt = $pdo->prepare("UPDATE form_options SET icon_path = ? WHERE id = ?");
            $stmt->execute([$icon_path, $id]);
        }
    }

    $sql = "UPDATE form_options SET name = ?, parent_id = ?, requires_brand_model = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $parent_id, $requires_brand_model, $id]);
}


// Ação de Excluir
if ($action === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // A exclusão em cascata (ON DELETE CASCADE) no banco de dados cuidará dos filhos.
    $sql = "DELETE FROM form_options WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
}

// Redireciona de volta para a página de gerenciamento
header('Location: form_manager.php');
exit();
?>