<?php
include 'check_auth.php';
include '../config/database.php';

// Apenas Admins podem acessar e a requisição deve ser POST
if ($_SESSION['user_level'] !== 'Admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$action = $_POST['action'] ?? '';

try {
    // Ação para Adicionar um novo método de pagamento
    if ($action === 'add') {
        $name = trim($_POST['name']);
        if (empty($name)) {
            header('Location: pagamentos_manager.php?status=error_name');
            exit();
        }
        
        $logo_path = null;

        // Lógica de Upload da Imagem
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload_dir = '../assets/uploads/payment_logos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['png', 'jpg', 'jpeg', 'svg'];

            if (in_array($file_ext, $allowed_exts)) {
                $file_name = 'logo_' . uniqid() . '.' . $file_ext;
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
                    $logo_path = 'assets/uploads/payment_logos/' . $file_name;
                }
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO payment_methods (name, logo_path) VALUES (?, ?)");
        $stmt->execute([$name, $logo_path]);
        
        header('Location: pagamentos_manager.php?status=success_add');

    // Ação para Excluir um método
    } elseif ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        // Opcional: deletar arquivo do logo do servidor
        $stmt = $pdo->prepare("SELECT logo_path FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);
        $logo = $stmt->fetchColumn();
        if ($logo && file_exists('../' . $logo)) {
            unlink('../' . $logo);
        }
        
        $delete_stmt = $pdo->prepare("DELETE FROM payment_methods WHERE id = ?");
        $delete_stmt->execute([$id]);

        header('Location: pagamentos_manager.php?status=success_delete');

    // Ação para Ativar/Desativar um método
    } elseif ($action === 'toggle_status') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        $stmt = $pdo->prepare("SELECT is_active FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);
        $current_status = $stmt->fetchColumn();
        
        $new_status = $current_status ? 0 : 1;

        $update_stmt = $pdo->prepare("UPDATE payment_methods SET is_active = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $id]);
        
        header('Location: pagamentos_manager.php?status=success_toggle');
    }

} catch (PDOException $e) {
    // Em produção, logar o erro em vez de exibi-lo
    // error_log("Erro no pagamentos_handler: " . $e->getMessage());
    die("Erro ao processar a solicitação: " . $e->getMessage());
}

exit();