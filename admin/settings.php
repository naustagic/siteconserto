<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
include 'templates/header.php';

// --- LÓGICA DE PREPARAÇÃO ---

// Definição dos temas em PHP
$themes = [
    'default' => [
        'name' => 'Padrão ReparoPRO',
        'colors' => [
            '--cor-primaria' => '#2563EB',
            '--cor-fundo' => '#F9FAFB',
            '--cor-texto' => '#1F2937',
            '--cor-fundo-card' => '#FFFFFF',
            '--fundo-card-rgb' => '255, 255, 255'
        ]
    ],
    'dark' => [
        'name' => 'Modo Escuro',
        'colors' => [
            '--cor-primaria' => '#60A5FA',
            '--cor-fundo' => '#111827',
            '--cor-texto' => '#F9FAFB',
            '--cor-fundo-card' => '#1F2937',
            '--fundo-card-rgb' => '31, 41, 55'
        ]
    ],
    'ocean' => [
        'name' => 'Oceano',
        'colors' => [
            '--cor-primaria' => '#0891B2',
            '--cor-fundo' => '#F0F9FF',
            '--cor-texto' => '#083344',
            '--cor-fundo-card' => '#FFFFFF',
            '--fundo-card-rgb' => '255, 255, 255'
        ]
    ],
    'apmidias' => [
        'name' => 'AP Mídias (Oficial)',
        'colors' => [
            '--cor-primaria' => '#F26522',
            '--cor-fundo' => '#002D9C',
            '--cor-texto' => '#FFFFFF',
            '--cor-fundo-card' => '#0A3A95',
            '--fundo-card-rgb' => '10, 58, 149'
        ]
    ],
    'apmidias-light' => [
        'name' => 'AP Mídias (Claro)',
        'colors' => [
            '--cor-primaria' => '#002D9C',
            '--cor-fundo' => '#F8F9FA',
            '--cor-texto' => '#212529',
            '--cor-fundo-card' => '#FFFFFF',
            '--fundo-card-rgb' => '255, 255, 255'
        ]
    ],
];

// Busca todas as configurações e banners do banco de dados
$current_theme = get_config($pdo, 'site_theme', 'default');
$current_logo = get_config($pdo, 'logo_path');
$guia_envio = get_config($pdo, 'guia_envio_conteudo');
$telegram_token = get_config($pdo, 'telegram_token');
$telegram_chat_id = get_config($pdo, 'telegram_chat_id');
$facebook_pixel_id = get_config($pdo, 'facebook_pixel_id');
$banners = $pdo->query("SELECT * FROM hero_banners ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// --- INÍCIO DA ADIÇÃO: Buscar novas configs de background ---
$body_bg_enabled = get_config($pdo, 'body_bg_enabled', '0');
$body_bg_image = get_config($pdo, 'body_bg_image_path', '');
$body_bg_opacity = get_config($pdo, 'body_bg_overlay_opacity', '0.75');
// --- FIM DA ADIÇÃO ---

// --- INÍCIO DA ADIÇÃO: Buscar novas configs de Identidade Visual ---
$site_title = get_config($pdo, 'site_title', 'ReparoPRO');
$brand_name_text = get_config($pdo, 'brand_name_text', '');
$brand_name_color = get_config($pdo, 'brand_name_color', '#FFFFFF');
// --- FIM DA ADIÇÃO ---


// Textos padrão para o formulário de upload de banner
$default_title = 'Seu Dispositivo em Mãos de Especialistas';
$default_subtitle = 'Reparos rápidos e com garantia para Consoles, PCs, Notebooks, Celulares e mais.';
?>
<script src="https://cdn.tiny.cloud/1/0n1legiupqthwju6nqh5wgex8d172xkouygln37zhomz92dp/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: 'textarea#guia-envio',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 300
    });
</script>

<h1 class="text-3xl font-bold mb-6">Configurações do Site</h1>

<?php
// Exibe mensagem de sucesso, se houver
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">';
    echo '<span class="block sm:inline">' . sanitize_output($_SESSION['success_message']) . '</span>';
    echo '</div>';
    unset($_SESSION['success_message']);
}
// Exibe mensagem de erro, se houver
if (isset($_SESSION['error_message'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
    echo '<span class="block sm:inline">' . sanitize_output($_SESSION['error_message']) . '</span>';
    echo '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-6" id="settings-tabs">
        <a href="#aparencia" data-tab="aparencia" class="tab-link active-tab py-4 px-1 border-b-2 font-medium text-sm">Tema e Aparência</a>
        <a href="#banners" data-tab="banners" class="tab-link py-4 px-1 border-b-2 font-medium text-sm">Banners (Carrossel)</a>
        <a href="#conteudo" data-tab="conteudo" class="tab-link py-4 px-1 border-b-2 font-medium text-sm">Conteúdo & Integrações</a>
    </nav>
</div>

<div id="aparencia" class="tab-pane active">
    <form action="settings_handler.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow-md card">
        <h2 class="text-xl font-bold mb-4">Tema Global do Site</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <?php foreach ($themes as $key => $theme): ?>
            <label for="theme_<?php echo $key; ?>" class="theme-card border-2 rounded-lg p-4 cursor-pointer transition-all <?php echo $current_theme === $key ? 'border-indigo-600 ring-2 ring-indigo-300' : 'border-gray-200'; ?>">
                <input type="radio" id="theme_<?php echo $key; ?>" name="site_theme" value="<?php echo $key; ?>" class="hidden" <?php echo $current_theme === $key ? 'checked' : ''; ?>>
                <h3 class="font-bold mb-2"><?php echo $theme['name']; ?></h3>
                <div class="flex gap-2">
                    <?php foreach($theme['colors'] as $color): ?><div style="background-color: <?php echo $color; ?>" class="w-6 h-6 rounded-full border"></div><?php endforeach; ?>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
        
        <h2 class="text-xl font-bold mb-4 mt-8 border-t pt-6">Logo</h2>
        <input type="file" name="logo" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <?php if ($current_logo): ?><img src="../<?php echo sanitize_output($current_logo); ?>" alt="Logo Atual" class="mt-4 h-12 bg-gray-100 p-2 rounded"><?php endif; ?>
        
        <div class="mt-8 border-t pt-6">
            <h2 class="text-xl font-bold mb-4">Identidade Visual (Título e Marca)</h2>
            <div class="space-y-4">
                <div>
                    <label for="site_title" class="block text-sm font-medium">Título do Site (Aba do Navegador)</label>
                    <input type="text" id="site_title" name="site_title" value="<?php echo sanitize_output($site_title); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="brand_name_text" class="block text-sm font-medium">Nome da Marca (ao lado do logo)</label>
                    <input type="text" id="brand_name_text" name="brand_name_text" value="<?php echo sanitize_output($brand_name_text); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="brand_name_color" class="block text-sm font-medium">Cor do Nome da Marca</label>
                    <input type="color" id="brand_name_color" name="brand_name_color" value="<?php echo sanitize_output($brand_name_color); ?>" class="mt-1 h-10 w-20 block rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        </div>
        <div class="mt-8 border-t pt-6">
            <h2 class="text-xl font-bold mb-4">Plano de Fundo da Página Inicial</h2>
            
            <div class="space-y-6">
                <label for="body_bg_enabled" class="flex items-center">
                    <input type="checkbox" id="body_bg_enabled" name="body_bg_enabled" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" <?php echo $body_bg_enabled === '1' ? 'checked' : ''; ?>>
                    <span class="ml-2">Ativar imagem de fundo no corpo da página inicial</span>
                </label>

                <div>
                    <label for="body_bg_image" class="block text-sm font-medium mb-1">Imagem de Fundo</label>
                    <input type="file" id="body_bg_image" name="body_bg_image" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <?php if ($body_bg_image): ?>
                        <div class="mt-4">
                           <img src="../<?php echo sanitize_output($body_bg_image); ?>" alt="Fundo Atual" class="h-20 rounded border p-1 bg-gray-100">
                           <a href="settings_handler.php?action=delete_body_bg" onclick="return confirm('Tem certeza que deseja remover a imagem de fundo?')" class="text-xs text-red-600 hover:underline">Remover imagem</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="body_bg_overlay_opacity" class="block text-sm font-medium">
                        Intensidade da Sobreposição (Overlay): <span id="opacity-value" class="font-bold brand-text"><?php echo ($body_bg_opacity * 100); ?>%</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Controla a "transparência" da cor de fundo sobre a imagem. Valores mais altos deixam o texto mais legível.</p>
                    <input type="range" id="body_bg_overlay_opacity" name="body_bg_overlay_opacity" min="0" max="1" step="0.05" value="<?php echo $body_bg_opacity; ?>" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                </div>
            </div>
        </div>
        
        <div class="mt-8 text-right"><button type="submit" name="save_appearance" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Salvar Aparência</button></div>
    </form>
</div>

<div id="banners" class="tab-pane hidden">
    <div class="bg-white p-6 rounded-lg shadow-md mb-6 card">
        <h2 class="text-xl font-bold mb-4">Adicionar Novo(s) Banner(s)</h2>
        <p class="text-sm text-gray-600 mb-4">As imagens serão redimensionadas para a proporção 16:9 (ideal 1920x1080px).</p>
        <form action="banner_handler.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="action" value="add_banner">
            <input type="file" name="banner_image[]" required multiple class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <input type="text" name="title" placeholder="Título do Banner" value="<?php echo $default_title; ?>" class="w-full p-2 border rounded">
            <textarea name="subtitle" placeholder="Subtítulo do Banner" rows="2" class="w-full p-2 border rounded"><?php echo $default_subtitle; ?></textarea>
            <button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Adicionar Banner(s)</button>
        </form>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md card">
        <h2 class="text-xl font-bold mb-4">Banners Atuais</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach($banners as $banner): ?>
                <div class="border rounded-lg overflow-hidden relative group">
                    <img src="../<?php echo sanitize_output($banner['image_path']); ?>" class="w-full h-32 object-cover">
                    <div class="p-2">
                        <p class="font-bold truncate" title="<?php echo sanitize_output($banner['title']); ?>"><?php echo sanitize_output($banner['title'] ?: 'Banner sem título'); ?></p>
                    </div>
                    <div class="absolute top-2 right-2 flex gap-2">
                        <button type="button" class="edit-banner-btn bg-blue-600 text-white rounded-full h-7 w-7 flex items-center justify-center text-xs"
                                data-id="<?php echo $banner['id']; ?>"
                                data-title="<?php echo sanitize_output($banner['title']); ?>"
                                data-subtitle="<?php echo sanitize_output($banner['subtitle']); ?>">
                            ✏️
                        </button>
                        <a href="banner_handler.php?action=delete_banner&id=<?php echo $banner['id']; ?>" onclick="return confirm('Tem certeza?')" class="bg-red-600 text-white rounded-full h-7 w-7 flex items-center justify-center text-xs">&times;</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="conteudo" class="tab-pane hidden">
    <form action="content_handler.php" method="POST" class="bg-white p-6 rounded-lg shadow-md space-y-8 card">
        <div>
            <h2 class="text-xl font-bold">Conteúdo da Página "Guia de Envio"</h2>
            <textarea id="guia-envio" name="guia_envio_conteudo" rows="10"><?php echo htmlspecialchars($guia_envio ?? ''); ?></textarea>
        </div>
        <div class="border-t pt-6">
            <h2 class="text-xl font-bold mb-4">Integrações</h2>
            <div class="space-y-4">
                <div>
                    <label for="telegram_token" class="block text-sm font-medium">Token do Bot do Telegram</label>
                    <input type="text" id="telegram_token" name="telegram_token" value="<?php echo sanitize_output($telegram_token ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="telegram_chat_id" class="block text-sm font-medium">Chat ID do Telegram</label>
                    <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?php echo sanitize_output($telegram_chat_id ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="facebook_pixel_id" class="block text-sm font-medium">ID do Pixel do Facebook/Instagram</label>
                    <input type="text" id="facebook_pixel_id" name="facebook_pixel_id" value="<?php echo sanitize_output($facebook_pixel_id ?? ''); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>
        </div>
        <div class="mt-8 text-right"><button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Salvar Conteúdo e Integrações</button></div>
    </form>
</div>

<div id="edit-banner-modal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg card">
        <h2 class="text-xl font-bold mb-4">Editar Textos do Banner</h2>
        <form action="banner_handler.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="edit_banner">
            <input type="hidden" name="banner_id" id="modal-banner-id">
            <div>
                <label for="modal-title" class="block text-sm font-medium">Título</label>
                <input type="text" name="title" id="modal-title" class="w-full mt-1 p-2 border rounded">
            </div>
            <div>
                <label for="modal-subtitle" class="block text-sm font-medium">Subtítulo</label>
                <textarea name="subtitle" id="modal-subtitle" rows="3" class="w-full mt-1 p-2 border rounded"></textarea>
            </div>
            <div class="flex justify-end gap-4 pt-4">
                <button type="button" id="modal-cancel-btn" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-md">Cancelar</button>
                <button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script para feedback visual do tema
    const themeCards = document.querySelectorAll('.theme-card');
    themeCards.forEach(card => {
        card.addEventListener('click', () => {
            themeCards.forEach(c => {
                c.classList.remove('border-indigo-600', 'ring-2', 'ring-indigo-300');
                c.classList.add('border-gray-200');
            });
            card.classList.add('border-indigo-600', 'ring-2', 'ring-indigo-300');
            card.classList.remove('border-gray-200');
        });
    });

    // Lógica das abas
    const tabs = document.querySelectorAll('.tab-link');
    const panes = document.querySelectorAll('.tab-pane');
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'aparencia';

    function showTab(tabId) {
        tabs.forEach(tab => {
            const tabLink = tab;
            if (tabLink.dataset.tab === tabId) {
                tabLink.classList.add('border-indigo-500', 'text-indigo-600');
                tabLink.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            } else {
                tabLink.classList.remove('border-indigo-500', 'text-indigo-600');
                tabLink.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        });
        panes.forEach(pane => {
            pane.classList.toggle('hidden', pane.id !== tabId);
        });
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const tabId = this.dataset.tab;
            window.history.pushState({tab: tabId}, '', '?tab=' + tabId);
            showTab(tabId);
        });
    });
    
    showTab(activeTab);
    
    // Lógica para o modal de edição de banner
    const modal = document.getElementById('edit-banner-modal');
    if (modal) {
        const modalForm = modal.querySelector('form');
        const editButtons = document.querySelectorAll('.edit-banner-btn');
        const cancelBtn = document.getElementById('modal-cancel-btn');

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const title = btn.dataset.title;
                const subtitle = btn.dataset.subtitle;

                modalForm.querySelector('#modal-banner-id').value = id;
                modalForm.querySelector('#modal-title').value = title;
                modalForm.querySelector('#modal-subtitle').value = subtitle;
                
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });
    }
    
    // --- INÍCIO DA ADIÇÃO: SCRIPT PARA O SLIDER DE OPACIDADE ---
    const opacitySlider = document.getElementById('body_bg_overlay_opacity');
    const opacityValueSpan = document.getElementById('opacity-value');
    if (opacitySlider && opacityValueSpan) {
        opacitySlider.addEventListener('input', function() {
            const percentage = Math.round(this.value * 100);
            opacityValueSpan.textContent = percentage + '%';
        });
    }
    // --- FIM DA ADIÇÃO ---
});
</script>

<?php include 'templates/footer.php'; ?>