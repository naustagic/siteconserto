<?php
// Este header já assume que a sessão foi verificada pelo check_auth.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// --- LÓGICA DE TEMA ---
$themes = [
    'default' => ['--cor-primaria' => '#2563EB', '--cor-fundo' => '#F3F4F6', '--cor-texto' => '#1F2937', '--cor-fundo-card' => '#FFFFFF', '--cor-sidebar' => '#1F2937'],
    'dark' => ['--cor-primaria' => '#60A5FA', '--cor-fundo' => '#111827', '--cor-texto' => '#F9FAFB', '--cor-fundo-card' => '#1F2937', '--cor-sidebar' => '#111827'],
    'ocean' => ['--cor-primaria' => '#0891B2', '--cor-fundo' => '#ECFEFF', '--cor-texto' => '#083344', '--cor-fundo-card' => '#FFFFFF', '--cor-sidebar' => '#164E63'],
];
$current_theme_key = get_config($pdo, 'site_theme', 'default');
$current_theme = $themes[$current_theme_key] ?? $themes['default'];
// --- FIM DA LÓGICA DE TEMA ---

$current_page = basename($_SERVER['PHP_SELF']);
$is_dark_theme = ($current_theme_key === 'dark' || $current_theme_key === 'ocean');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Painel Administrativo - ReparoPRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            <?php foreach($current_theme as $key => $value) { echo "$key: $value;"; } ?>
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--cor-fundo); color: var(--cor-texto); }
        .brand-bg { background-color: var(--cor-primaria); }
        .brand-text { color: var(--cor-primaria); }
        .sidebar-link { display: block; padding: 0.75rem 1rem; border-radius: 0.375rem; transition: background-color 0.2s; }
        .sidebar-link:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar-link.active { background-color: var(--cor-primaria); color: white; font-weight: bold; }
        .card { background-color: var(--cor-fundo-card); }

        <?php if ($is_dark_theme): ?>
        .bg-white { background-color: var(--cor-fundo-card) !important; }
        .text-gray-500 { color: #9CA3AF !important; }
        .text-gray-600, .text-gray-700 { color: #E5E7EB !important; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="color"], select, textarea { background-color: #374151; border-color: #4B5563; color: var(--cor-texto); }
        input::placeholder, textarea::placeholder { color: #9CA3AF; }
        table thead { background-color: #374151 !important; border-color: #4B5563 !important; }
        table tbody tr:hover { background-color: #4B5563 !important; }
        table tr, table tbody { border-color: #4B5563 !important; }
        #modal-cancel-btn { background-color: #4B5563 !important; color: #F9FAFB !important; }
        #modal-cancel-btn:hover { background-color: #6B7280 !important; }
        <?php endif; ?>

        #notification-bell { position: relative; cursor: pointer; }
        #notification-badge {
            position: absolute; top: -5px; right: -8px; min-width: 20px; height: 20px;
            border-radius: 50%; background-color: #EF4444; color: white;
            font-size: 12px; font-weight: bold; display: flex; align-items: center; justify-content: center;
            transform: scale(0); transition: transform 0.2s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }
        #notification-badge.active { transform: scale(1); }
    </style>
</head>
<body class="<?php echo ($is_dark_theme) ? 'dark-theme' : ''; ?>">

<div class="flex h-screen overflow-hidden">
    <aside class="w-64 text-white flex-shrink-0 flex flex-col" style="background-color: var(--cor-sidebar);">
        <div class="p-4 border-b border-gray-700">
            <h1 class="text-2xl font-bold text-center">Reparo<span class="brand-text">PRO</span></h1>
        </div>
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <a href="index.php" class="sidebar-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">Dashboard</a>
            <a href="os_list.php" class="sidebar-link <?php echo in_array($current_page, ['os_list.php', 'os_details.php']) ? 'active' : ''; ?>">Ordens de Serviço</a>
            <?php if ($_SESSION['user_level'] === 'Admin'): ?>
            <a href="home_manager.php" class="sidebar-link <?php echo ($current_page == 'home_manager.php') ? 'active' : ''; ?>">Página Inicial</a>
            <a href="settings.php" class="sidebar-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">Configurações</a>
            <a href="reviews_manager.php" class="sidebar-link <?php echo ($current_page == 'reviews_manager.php') ? 'active' : ''; ?>">Avaliações</a>
            <a href="social_links_manager.php" class="sidebar-link <?php echo ($current_page == 'social_links_manager.php') ? 'active' : ''; ?>">Redes Sociais</a>
            <a href="form_manager.php" class="sidebar-link <?php echo in_array($current_page, ['form_manager.php', 'form_option_edit.php']) ? 'active' : ''; ?>">Gestor de Formulário</a>
            <a href="logistics_manager.php" class="sidebar-link <?php echo ($current_page == 'logistics_manager.php') ? 'active' : ''; ?>">Logística e Status</a>
            <a href="reviews_manager.php" class="sidebar-link <?php echo ($current_page == 'reviews_manager.php') ? 'active' : ''; ?>">Avaliações</a>
            <?php endif; ?>
        </nav>
        <div class="p-4 border-t border-gray-700">
            <p class="text-sm">Logado como:</p>
            <p class="font-bold"><?php echo sanitize_output($_SESSION['user_name']); ?></p>
            <a href="logout.php" class="text-red-400 hover:text-red-300 text-sm mt-2 block">Sair</a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm card p-4 flex justify-between items-center flex-shrink-0">
            <div id="page-title-container"></div>
            <div id="notification-bell">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span id="notification-badge" class="hidden">0</span>
            </div>
        </header>

        <main class="flex-1 p-8 overflow-y-auto">