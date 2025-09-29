<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
include 'templates/header.php';

// Busca todos os dados para exibir e editar
$section_titles = [];
$stmt = $pdo->query("SELECT config_key, config_value FROM config_site WHERE config_key LIKE 'section_%'");
while ($row = $stmt->fetch()) { $section_titles[$row['config_key']] = $row['config_value']; }

$services = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$how_it_works_steps = $pdo->query("SELECT * FROM how_it_works ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-3xl font-bold mb-6">Gerenciador da Página Inicial</h1>

<?php // Mensagens de sucesso ou erro
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert"><p>' . sanitize_output($_SESSION['success_message']) . '</p></div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert"><p>' . sanitize_output($_SESSION['error_message']) . '</p></div>';
    unset($_SESSION['error_message']);
}
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-8 card">
    <h2 class="text-xl font-bold mb-4">Títulos das Seções</h2>
    <form action="home_handler.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <input type="hidden" name="action" value="update_titles">
        <div>
            <label class="block font-medium">Título (Serviços)</label>
            <input type="text" name="section_services_title" value="<?php echo sanitize_output($section_titles['section_services_title'] ?? ''); ?>" class="w-full mt-1 p-2 border rounded">
        </div>
        <div>
            <label class="block font-medium">Subtítulo (Serviços)</label>
            <input type="text" name="section_services_subtitle" value="<?php echo sanitize_output($section_titles['section_services_subtitle'] ?? ''); ?>" class="w-full mt-1 p-2 border rounded">
        </div>
        <div>
            <label class="block font-medium">Título (Como Funciona)</label>
            <input type="text" name="section_howitworks_title" value="<?php echo sanitize_output($section_titles['section_howitworks_title'] ?? ''); ?>" class="w-full mt-1 p-2 border rounded">
        </div>
        <div>
            <label class="block font-medium">Subtítulo (Como Funciona)</label>
            <input type="text" name="section_howitworks_subtitle" value="<?php echo sanitize_output($section_titles['section_howitworks_subtitle'] ?? ''); ?>" class="w-full mt-1 p-2 border rounded">
        </div>
        <div class="md:col-span-2 text-right">
            <button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Salvar Títulos</button>
        </div>
    </form>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mb-8 card">
    <h2 class="text-xl font-bold mb-4">Cards de Serviço</h2>
    <p class="text-sm opacity-70 mb-4">Adicione, edite ou remova os serviços exibidos na página inicial. Para os ícones, use o código de um SVG com `w-6 h-6`. Sites como <a href="https://heroicons.com/" target="_blank" class="brand-text underline">Heroicons</a> são ótimos.</p>
    <table class="w-full text-sm mb-6">
        <tbody>
            <?php foreach($services as $item): ?>
            <tr class="border-t"><td class="p-2 flex items-center gap-4">
                <div class="w-10 h-10 brand-bg text-white rounded-lg flex items-center justify-center flex-shrink-0"><?php echo $item['icon_svg']; ?></div>
                <div><strong class="block"><?php echo sanitize_output($item['title']); ?></strong><?php echo sanitize_output($item['description']); ?></div>
            </td><td class="p-2 text-right whitespace-nowrap">
                <button type="button" class="edit-btn text-blue-500 font-medium hover:underline"
                    data-id="<?php echo $item['id']; ?>"
                    data-type="service"
                    data-title="<?php echo sanitize_output($item['title']); ?>"
                    data-description="<?php echo sanitize_output($item['description']); ?>"
                    data-icon="<?php echo htmlentities($item['icon_svg']); ?>"
                    data-order="<?php echo $item['sort_order']; ?>">Editar</button>
                <a href="home_handler.php?action=delete_service&id=<?php echo $item['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este serviço?')" class="text-red-500 font-medium hover:underline ml-4">Excluir</a>
            </td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <details>
        <summary class="font-bold cursor-pointer">Adicionar Novo Serviço</summary>
        <form action="home_handler.php" method="POST" class="mt-4 space-y-4 border-t pt-4">
            <input type="hidden" name="action" value="add_service">
            <input type="text" name="title" placeholder="Título do Serviço" required class="w-full p-2 border rounded">
            <textarea name="description" placeholder="Descrição do Serviço" required class="w-full p-2 border rounded"></textarea>
            <textarea name="icon_svg" placeholder="Código SVG do Ícone (completo)" required class="w-full p-2 border rounded font-mono text-xs h-24"></textarea>
            <input type="number" name="sort_order" placeholder="Ordem (ex: 1, 2, 3)" value="0" class="w-full p-2 border rounded">
            <button type="submit" class="brand-bg text-white font-bold py-2 px-4 rounded-md">Adicionar</button>
        </form>
    </details>
</div>

<div class="bg-white p-6 rounded-lg shadow-md card">
    <h2 class="text-xl font-bold mb-4">Passos de "Como Funciona"</h2>
    <table class="w-full text-sm mb-6">
        <tbody>
            <?php foreach($how_it_works_steps as $item): ?>
            <tr class="border-t"><td class="p-2 flex items-center gap-4">
                <div class="w-10 h-10 brand-bg text-white rounded-full flex items-center justify-center flex-shrink-0 font-bold text-lg"><?php echo $item['sort_order']; ?></div>
                <div><strong class="block"><?php echo sanitize_output($item['title']); ?></strong><?php echo sanitize_output($item['description']); ?></div>
            </td><td class="p-2 text-right whitespace-nowrap">
                <button type="button" class="edit-btn text-blue-500 font-medium hover:underline"
                    data-id="<?php echo $item['id']; ?>"
                    data-type="howitworks"
                    data-title="<?php echo sanitize_output($item['title']); ?>"
                    data-description="<?php echo sanitize_output($item['description']); ?>"
                    data-icon="<?php echo htmlentities($item['icon_svg']); ?>"
                    data-order="<?php echo $item['sort_order']; ?>">Editar</button>
                <a href="home_handler.php?action=delete_howitworks&id=<?php echo $item['id']; ?>" onclick="return confirm('Tem certeza que deseja excluir este passo?')" class="text-red-500 font-medium hover:underline ml-4">Excluir</a>
            </td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <details>
        <summary class="font-bold cursor-pointer">Adicionar Novo Passo</summary>
        <form action="home_handler.php" method="POST" class="mt-4 space-y-4 border-t pt-4">
            <input type="hidden" name="action" value="add_howitworks">
            <input type="text" name="title" placeholder="Título do Passo" required class="w-full p-2 border rounded">
            <textarea name="description" placeholder="Descrição do Passo" required class="w-full p-2 border rounded"></textarea>
            <textarea name="icon_svg" placeholder="Não usado para esta seção" readonly class="w-full p-2 border rounded bg-gray-100"></textarea>
            <input type="number" name="sort_order" placeholder="Ordem (ex: 1, 2, 3)" value="0" class="w-full p-2 border rounded">
            <button type="submit" class="brand-bg text-white font-bold py-2 px-4 rounded-md">Adicionar</button>
        </form>
    </details>
</div>

<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg card">
        <h2 id="modal-title" class="text-xl font-bold mb-4">Editar Item</h2>
        <form id="edit-form" action="home_handler.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" id="modal-action">
            <input type="hidden" name="id" id="modal-id">
            
            <input type="text" name="title" id="modal-title-input" placeholder="Título" required class="w-full p-2 border rounded">
            <textarea name="description" id="modal-description" placeholder="Descrição" required class="w-full p-2 border rounded"></textarea>
            <textarea name="icon_svg" id="modal-icon" placeholder="Código SVG do Ícone" class="w-full p-2 border rounded font-mono text-xs h-24"></textarea>
            <input type="number" name="sort_order" id="modal-order" placeholder="Ordem" class="w-full p-2 border rounded">

            <div class="flex justify-end gap-4 pt-4">
                <button type="button" id="modal-cancel-btn" class="bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-md">Cancelar</button>
                <button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('edit-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalForm = document.getElementById('edit-form');
    const modalActionInput = document.getElementById('modal-action');
    const modalIdInput = document.getElementById('modal-id');
    const modalTitleInput = document.getElementById('modal-title-input');
    const modalDescriptionInput = document.getElementById('modal-description');
    const modalIconInput = document.getElementById('modal-icon');
    const modalOrderInput = document.getElementById('modal-order');
    const cancelBtn = document.getElementById('modal-cancel-btn');

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            // Pega os dados do botão clicado
            const id = btn.dataset.id;
            const type = btn.dataset.type;
            const title = btn.dataset.title;
            const description = btn.dataset.description;
            const icon = btn.dataset.icon;
            const order = btn.dataset.order;
            
            // Configura e preenche o modal
            modalTitle.textContent = `Editar "${title}"`;
            modalActionInput.value = `edit_${type}`;
            modalIdInput.value = id;
            modalTitleInput.value = title;
            modalDescriptionInput.value = description;
            modalOrderInput.value = order;
            
            // O campo de ícone só é relevante para 'service'
            const iconContainer = modalIconInput.parentElement;
            if (type === 'service') {
                modalIconInput.value = icon;
                iconContainer.style.display = 'block';
            } else {
                iconContainer.style.display = 'none';
            }

            // Exibe o modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    });

    // Função para fechar o modal
    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    };

    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            closeModal();
        }
    });
});
</script>

<?php include 'templates/footer.php'; ?>