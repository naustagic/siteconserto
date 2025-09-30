<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') {
    die("Acesso negado.");
}
require_once '../config/database.php';
require_once '../includes/functions.php';

// Função para atualizar ou inserir uma configuração
function update_config($pdo, $key, $value) {
    // Esta função usa a tabela 'config_site', conforme a estrutura original do seu projeto.
    $sql = "INSERT INTO config_site (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$key, $value, $value]);
}

// ======================================================
// 1. DEFINIÇÃO COMPLETA DOS TEMAS VÁLIDOS
// ======================================================
$themes = [
    'default' => ['name' => 'Padrão ReparoPRO'],
    'dark' => ['name' => 'Modo Escuro'],
    'ocean' => ['name' => 'Oceano'],
    'apmidias' => ['name' => 'AP Mídias (Oficial)'],
    'apmidias-light' => ['name' => 'AP Mídias (Claro)'],
];

// Lógica para remover a imagem de fundo via link GET
if (isset($_GET['action']) && $_GET['action'] === 'delete_body_bg') {
    $current_bg_path = get_config($pdo, 'body_bg_image_path');
    
    if ($current_bg_path && file_exists('../' . $current_bg_path)) {
        unlink('../' . $current_bg_path);
    }
    
    update_config($pdo, 'body_bg_image_path', '');
    update_config($pdo, 'body_bg_enabled', '0');
    
    $_SESSION['success_message'] = "Imagem de fundo removida com sucesso.";
    header("Location: settings.php?tab=aparencia");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = $_POST['action'] ?? '';

    // Processa o formulário principal da aba "Aparência"
    if (isset($_POST['save_appearance'])) {
        
        // Validação do tema
        if (isset($_POST['site_theme']) && array_key_exists($_POST['site_theme'], $themes)) {
            update_config($pdo, 'site_theme', $_POST['site_theme']);
        }

        // Processa o upload do novo Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/uploads/logo/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $old_logos = glob($upload_dir . "logo.*");
            if ($old_logos) {
                foreach($old_logos as $old_logo) {
                    unlink($old_logo);
                }
            }

            $file = $_FILES['logo'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
            
            if(in_array($file['type'], $allowed_mime_types)) {
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_filename = 'logo.' . $extension;
                $destination = $upload_dir . $new_filename;
        
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $db_path = 'assets/uploads/logo/' . $new_filename;
                    update_config($pdo, 'logo_path', $db_path);
                }
            }
        }

        // Processar as configurações de plano de fundo
        $bg_enabled = isset($_POST['body_bg_enabled']) ? '1' : '0';
        update_config($pdo, 'body_bg_enabled', $bg_enabled);

        if (isset($_FILES['body_bg_image']) && $_FILES['body_bg_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir_bg = '../assets/uploads/backgrounds/';
            if (!is_dir($upload_dir_bg)) {
                mkdir($upload_dir_bg, 0755, true);
            }

            $old_bg_path = get_config($pdo, 'body_bg_image_path');
            $file_bg = $_FILES['body_bg_image'];
            $allowed_mime_types_bg = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            
            if(in_array($file_bg['type'], $allowed_mime_types_bg)) {
                $extension_bg = strtolower(pathinfo($file_bg['name'], PATHINFO_EXTENSION));
                $new_filename_bg = 'bg-' . time() . '.' . $extension_bg;
                $destination_bg = $upload_dir_bg . $new_filename_bg;
        
                if (move_uploaded_file($file_bg['tmp_name'], $destination_bg)) {
                    if ($old_bg_path && file_exists('../' . $old_bg_path)) {
                        unlink('../' . $old_bg_path);
                    }
                    $db_path_bg = 'assets/uploads/backgrounds/' . $new_filename_bg;
                    update_config($pdo, 'body_bg_image_path', $db_path_bg);
                } else {
                    $_SESSION['error_message'] = "Falha ao mover a imagem de fundo.";
                    header("Location: settings.php?tab=aparencia");
                    exit;
                }
            } else {
                $_SESSION['error_message'] = "Formato de arquivo inválido para a imagem de fundo.";
                header("Location: settings.php?tab=aparencia");
                exit;
            }
        }

        if (isset($_POST['body_bg_overlay_opacity'])) {
            $opacity = floatval($_POST['body_bg_overlay_opacity']);
            if ($opacity >= 0 && $opacity <= 1) {
                update_config($pdo, 'body_bg_overlay_opacity', $opacity);
            }
        }
        
        // Salvar Título do Site e Nome/Cor da Marca
        if (isset($_POST['site_title'])) {
            update_config($pdo, 'site_title', htmlspecialchars($_POST['site_title'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($_POST['brand_name_text'])) {
            update_config($pdo, 'brand_name_text', htmlspecialchars($_POST['brand_name_text'], ENT_QUOTES, 'UTF-8'));
        }
        if (isset($_POST['brand_name_color']) && preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $_POST['brand_name_color'])) {
            update_config($pdo, 'brand_name_color', $_POST['brand_name_color']);
        }
        
        $_SESSION['success_message'] = 'Configurações de aparência salvas com sucesso!';
        header('Location: settings.php?tab=aparencia');
        exit();
    }

    // NOVO BLOCO: Processa o formulário da aba "Conteúdo e Integrações"
    elseif ($action === 'save_content_integrations') {
        
        // Salva o conteúdo do Guia de Envio
        if (isset($_POST['guia_envio_conteudo'])) {
            update_config($pdo, 'guia_envio_conteudo', $_POST['guia_envio_conteudo']);
        }

        // Salva o número do WhatsApp (removendo caracteres não numéricos)
        if (isset($_POST['whatsapp_number'])) {
            $whatsapp_number = preg_replace('/[^0-9]/', '', $_POST['whatsapp_number']);
            update_config($pdo, 'whatsapp_number', $whatsapp_number);
        }

        // Salva as integrações (Telegram, Facebook)
        if (isset($_POST['telegram_token'])) {
            update_config($pdo, 'telegram_token', trim($_POST['telegram_token']));
        }
        if (isset($_POST['telegram_chat_id'])) {
            update_config($pdo, 'telegram_chat_id', trim($_POST['telegram_chat_id']));
        }
        if (isset($_POST['facebook_pixel_id'])) {
            update_config($pdo, 'facebook_pixel_id', trim($_POST['facebook_pixel_id']));
        }

        $_SESSION['success_message'] = 'Configurações de conteúdo e integrações salvas com sucesso!';
        header('Location: settings.php?tab=conteudo');
        exit();
    }

    // Processa o formulário da aba "Avançado"
    elseif ($action === 'save_advanced') {
        if (isset($_POST['sold_display_days'])) {
            // Garante que o valor seja um inteiro não negativo antes de salvar
            $days = abs((int)$_POST['sold_display_days']);
            update_config($pdo, 'sold_display_days', $days);
        }
        $_SESSION['success_message'] = 'Configurações avançadas salvas com sucesso!';
        header('Location: settings.php?tab=avancado');
        exit();
    }
    
}

// Redireciona se o acesso não for via POST ou GET com a ação correta
header('Location: settings.php');
exit();
?>