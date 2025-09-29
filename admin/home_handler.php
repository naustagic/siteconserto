<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? null;

function update_config($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO config_site (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?");
    $stmt->execute([$key, $value, $value]);
}

try {
    switch ($action) {
        case 'update_titles':
            $keys = ['section_services_title', 'section_services_subtitle', 'section_howitworks_title', 'section_howitworks_subtitle', 'section_reviews_title', 'section_reviews_subtitle'];
            foreach ($keys as $key) {
                if (isset($_POST[$key])) {
                    update_config($pdo, $key, $_POST[$key]);
                }
            }
            $_SESSION['success_message'] = 'Títulos atualizados com sucesso!';
            break;

        case 'add_service':
            $stmt = $pdo->prepare("INSERT INTO services (icon_svg, title, description, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['icon_svg'], $_POST['title'], $_POST['description'], $_POST['sort_order'] ?? 0]);
            $_SESSION['success_message'] = 'Novo serviço adicionado!';
            break;

        case 'edit_service':
            $stmt = $pdo->prepare("UPDATE services SET icon_svg = ?, title = ?, description = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$_POST['icon_svg'], $_POST['title'], $_POST['description'], $_POST['sort_order'] ?? 0, $_POST['id']]);
            $_SESSION['success_message'] = 'Serviço atualizado!';
            break;

        case 'delete_service':
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $_SESSION['success_message'] = 'Serviço excluído!';
            break;

        case 'add_howitworks':
            $stmt = $pdo->prepare("INSERT INTO how_it_works (icon_svg, title, description, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['icon_svg'], $_POST['title'], $_POST['description'], $_POST['sort_order'] ?? 0]);
            $_SESSION['success_message'] = 'Novo passo adicionado!';
            break;

        case 'edit_howitworks':
            $stmt = $pdo->prepare("UPDATE how_it_works SET icon_svg = ?, title = ?, description = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$_POST['icon_svg'], $_POST['title'], $_POST['description'], $_POST['sort_order'] ?? 0, $_POST['id']]);
            $_SESSION['success_message'] = 'Passo atualizado!';
            break;

        case 'delete_howitworks':
            $stmt = $pdo->prepare("DELETE FROM how_it_works WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $_SESSION['success_message'] = 'Passo excluído!';
            break;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Ocorreu um erro: ' . $e->getMessage();
}

header('Location: home_manager.php');
exit();
?>