<?php
include 'check_auth.php';
include '../config/database.php';

// Sua consulta de produtos (inalterada)
$stmt = $pdo->query("
    SELECT 
        p.*, 
        pi.image_path,
        GROUP_CONCAT(DISTINCT pm.name SEPARATOR ', ') as payment_methods_summary
    FROM products p 
    LEFT JOIN (SELECT product_id, image_path FROM product_images WHERE is_main = 1) AS pi ON p.id = pi.product_id
    LEFT JOIN product_payment_prices ppp ON p.id = ppp.product_id
    LEFT JOIN payment_methods pm ON ppp.payment_method_id = pm.id AND pm.is_active = 1
    GROUP BY p.id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sua consulta de métodos de pagamento (inalterada)
$payment_methods = $pdo->query("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Busca as categorias cadastradas para popular o seletor
$categories = $pdo->query("SELECT name FROM product_categories ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

include 'templates/header.php'; 
?>

<style>
    /* Todos os seus estilos foram mantidos, sem nenhuma remoção. */
    .modal-content {
        max-width: 800px;
        width: 95%;
    }
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    .image-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
        padding: 1rem;
        background-color: #f7f7f7;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .img-thumbnail-container {
        position: relative;
        border: 3px solid transparent;
        border-radius: 5px;
        overflow: hidden;
        transition: border-color 0.3s;
        background-color: #e9e9e9;
        padding-bottom: 40px; 
    }
    .img-thumbnail-container.is-cover {
        border-color: #3b82f6;
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }
    .img-thumbnail-container img {
        width: 100%;
        height: 110px;
        object-fit: cover;
        display: block;
    }
    .img-actions {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 40px;
        background-color: rgba(0, 0, 0, 0.6);
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        font-size: 0.9rem;
    }
    .img-thumbnail-container:hover .img-actions {
        opacity: 1;
    }
    .img-actions button {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 4px;
        width: 100%;
        text-align: center;
    }
    .img-actions button:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
    .img-actions .fa-trash { color: #ef4444; }
    .img-actions .fa-star { color: #facc15; }
    .cover-indicator {
        position: absolute;
        bottom: 45px;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(59, 130, 246, 0.9);
        color: white;
        padding: 2px 8px;
        font-size: 0.7rem;
        font-weight: bold;
        border-radius: 4px;
        display: none;
        pointer-events: none;
    }
    .is-cover .cover-indicator {
        display: block;
    }
    .image-title-input {
        width: 100%;
        font-size: 0.8rem;
        padding: 6px;
        border: 1px solid #ccc;
        border-top: 2px solid #bbb;
        border-radius: 0 0 3px 3px;
        box-sizing: border-box;
        text-align: center;
        position: absolute;
        bottom: 0;
        left: 0;
    }
    #upload-feedback {
        font-size: 0.9rem;
        margin-top: 0.5rem;
        color: #3b82f6;
        display: none;
    }
    .display-mode-selector {
        background-color: #e9ecef;
        border-radius: 8px;
        padding: 4px;
        display: flex;
        justify-content: space-between;
    }
    .display-mode-selector label {
        flex: 1;
        text-align: center;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s ease, color 0.3s ease;
        font-weight: 500;
        font-size: 0.9rem;
        color: #495057;
    }
    .display-mode-selector input[type="radio"] {
        display: none;
    }
    .display-mode-selector input[type="radio"]:checked + label {
        background-color: #3b82f6;
        color: white;
        box-shadow: 0 2px 5px rgba(59, 130, 246, 0.3);
    }
    
    [data-tooltip] {
        position: relative;
        cursor: pointer;
    }
    [data-tooltip]::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 125%; 
        left: 50%;
        transform: translateX(-50%);
        background-color: #2d3748; 
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, transform 0.2s ease;
        pointer-events: none;
        z-index: 1000;
    }
    [data-tooltip]:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-5px); 
    }
    .table th, .table td {
        white-space: nowrap;
    }
    .table th:nth-child(2), .table td:nth-child(2),
    .table th:nth-child(3), .table td:nth-child(3) {
        white-space: normal;
    }
    .actions-column {
        width: 1%;
    }
</style>

<div class="container mx-auto">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gerenciador de Produtos</h1>
        <div>
            <a href="categorias_manager.php" class="flex items-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded transition-colors inline-flex">
                <i class="fas fa-tags mr-2"></i>Gerenciar Categorias
            </a>
            <button class="flex items-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition-colors inline-flex" onclick="openProductModal()">
                <i class="fas fa-plus-circle mr-2"></i>Adicionar Produto
            </button>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><i class="fas fa-list-alt"></i>Produtos Cadastrados</div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th class="text-center">Imagem</th>
                            <th>Produto</th>
                            <th>Preços</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Anúncio</th>
                            <th class="text-center">Destaque</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr class="<?= $product['is_active'] ? '' : 'opacity-60 bg-gray-50' ?>">
                                <td class="text-center"><img src="../<?= htmlspecialchars($product['image_path'] ?? 'https://placehold.co/100x100/e0e0e0/777?text=Sem+Foto') ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image"></td>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($product['discount_price']) && $product['discount_price'] > 0): ?>
                                        <span class="text-gray-500 line-through">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                        <strong class="text-green-600 block">R$ <?= number_format($product['discount_price'], 2, ',', '.') ?></strong>
                                    <?php else: ?>
                                        <strong>R$ <?= number_format($product['price'], 2, ',', '.') ?></strong>
                                    <?php endif; ?>
                                    <?php if (!empty($product['payment_methods_summary'])): ?><div class="price-summary">Pagamentos: <?= htmlspecialchars($product['payment_methods_summary']) ?></div><?php endif; ?>
                                </td>
                                <td class="text-center sold-status-cell">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $product['is_sold'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>"><?= $product['is_sold'] ? 'Vendido' : 'Disponível' ?></span>
                                </td>
                                <td class="text-center active-status-cell">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-800' ?>">
                                        <?= $product['is_active'] ? 'Ativo' : 'Inativo' ?>
                                    </span>
                                </td>
                                <td class="text-center"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= $product['is_featured'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>"><?= $product['is_featured'] ? 'Sim' : 'Não' ?></span></td>
                                
                                <td class="text-right actions-column">
                                    <button class="text-blue-500 hover:text-blue-700" data-tooltip="Editar Produto" onclick="openProductModal(<?= $product['id'] ?>)"><i class="fas fa-edit"></i></button>
                                    <button class="text-gray-500 hover:text-gray-700" data-tooltip="Preços Específicos" onclick="openPricesModal(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')"><i class="fas fa-dollar-sign"></i></button>
                                    
                                    <form action="produtos_handler.php" method="POST" class="inline-block ajax-toggle-form" data-tooltip="<?= $product['is_active'] ? 'Desativar Anúncio' : 'Ativar Anúncio' ?>">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="<?= $product['is_active'] ? 'text-green-500 hover:text-green-700' : 'text-gray-400 hover:text-gray-600' ?>">
                                            <i class="fas <?= $product['is_active'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                        </button>
                                    </form>

                                    <form action="produtos_handler.php" method="POST" class="inline-block ajax-toggle-form" data-tooltip="<?= $product['is_sold'] ? 'Marcar Disponível' : 'Marcar Vendido' ?>">
                                        <input type="hidden" name="action" value="toggle_sold">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="<?= $product['is_sold'] ? 'text-green-500 hover:text-green-700' : 'text-yellow-500 hover:text-yellow-700' ?>">
                                            <i class="fas fa-toggle-<?= $product['is_sold'] ? 'off' : 'on' ?>"></i>
                                        </button>
                                    </form>

                                    <form action="produtos_handler.php" method="POST" class="inline-block" data-tooltip="Excluir Produto" onsubmit="return confirm('Tem certeza?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="productModal" class="modal">
    <div class="modal-content">
        <form id="productForm" action="produtos_handler.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle"></h5>
                <button type="button" class="close-button" onclick="closeProductModal()">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" id="productAction">
                <input type="hidden" name="product_id" id="productId">
                <div class="form-group"><label for="name">Nome do Produto</label><input type="text" class="form-control" name="name" id="name" required></div>
                <div class="form-group">
                    <label for="category" class="flex items-center">
                        <span>Categoria</span>
                        <a href="categorias_manager.php" class="text-xs text-blue-500 hover:underline ml-auto" target="_blank" title="Gerenciar categorias em uma nova aba">
                            <i class="fas fa-plus-circle mr-1"></i>Gerenciar Categorias
                        </a>
                    </label>
                    <select class="form-control" name="category" id="category">
                        <option value="">Nenhuma categoria selecionada</option>
                        <?php foreach ($categories as $category_name): ?>
                            <option value="<?= htmlspecialchars($category_name) ?>"><?= htmlspecialchars($category_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label for="description">Descrição Completa</label><textarea class="form-control" name="description" id="description" rows="4" required></textarea></div>
                <div class="form-row"><div class="form-group"><label for="price">Preço Base (R$)</label><input type="text" class="form-control" name="price" id="price" placeholder="1500,00" required></div><div class="form-group"><label for="base_payment_method_id">Forma do Preço Base</label><select name="base_payment_method_id" id="base_payment_method_id" class="form-control"><option value="">(Nenhuma)</option><?php foreach($payment_methods as $method) echo "<option value='{$method['id']}'>" . htmlspecialchars($method['name']) . "</option>"; ?></select></div></div>
                <div class="form-group"><label for="discount_price">Preço com Desconto (Opcional)</label><input type="text" class="form-control" name="discount_price" id="discount_price" placeholder="1399,90"></div>
                <hr class="my-4">
                <div class="form-group">
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 rounded mr-2" name="is_active" id="is_active" value="1" checked>
                        <label for="is_active" class="mb-0">Anúncio Ativo (visível no site)</label>
                    </div>
                </div>
                <div class="form-group">
                    <div class="flex items-center">
                        <input type="checkbox" class="h-4 w-4 rounded mr-2" name="is_featured" id="is_featured" value="1">
                        <label for="is_featured" class="mb-0">Marcar como Destaque/Promoção na Home</label>
                    </div>
                </div>
                <hr class="my-4">
                <div class="font-bold mb-2">Gerenciamento de Imagens</div>
                <div class="form-group">
                    <label class="block mb-2 text-sm font-medium text-gray-700">Modo de Exibição na Página Inicial</label>
                    <div class="display-mode-selector">
                        <input type="radio" id="mode_static" name="image_display_mode" value="static" checked>
                        <label for="mode_static"><i class="fas fa-image mr-2"></i>Capa Fixa</label>
                        <input type="radio" id="mode_rotate" name="image_display_mode" value="rotate">
                        <label for="mode_rotate"><i class="fas fa-sync-alt mr-2"></i>Rotação Automática</label>
                    </div>
                </div>
                <div id="imageGallery" class="image-gallery-grid"></div>
                <div class="form-group mt-4">
                    <label for="new_images" class="block font-medium">Adicionar Novas Imagens</label>
                    <input type="file" class="form-control" name="new_images[]" id="new_images" multiple accept="image/*">
                    <small class="text-gray-500 mt-1 block">
                        <b>Ao adicionar:</b> as imagens são pré-visualizadas e enviadas ao salvar.<br>
                        <b>Ao editar:</b> o upload é automático após selecionar as imagens.
                    </small>
                    <div id="upload-feedback"><i class="fas fa-spinner fa-spin mr-2"></i>Enviando imagens...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 px-4 rounded" onclick="closeProductModal()">Cancelar</button>
                <button type="submit" id="saveProductBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Salvar Produto</button>
            </div>
        </form>
    </div>
</div>
<div id="pricesModal" class="modal"></div>


<script>
// --- Todo o seu JavaScript anterior para os modais foi mantido ---
const productModal = document.getElementById('productModal');
const pricesModal = document.getElementById('pricesModal');
const paymentMethodsData = <?php echo json_encode($payment_methods); ?>;

// ... (todas as suas funções openProductModal, renderImageGallery, etc. continuam aqui, inalteradas)
function openProductModal(id = null) {
    const form = document.getElementById('productForm');
    form.reset();
    document.getElementById('is_active').checked = true;
    document.getElementById('productModalTitle').innerText = id ? 'Editar Produto' : 'Adicionar Novo Produto';
    document.getElementById('productAction').value = id ? 'edit' : 'add';
    document.getElementById('productId').value = id || '';
    document.getElementById('mode_static').checked = true;
    const imageGallery = document.getElementById('imageGallery');
    imageGallery.innerHTML = '<div class="p-4 text-center text-gray-500">Nenhuma imagem selecionada.</div>';
    document.getElementById('new_images').value = '';
    if (id) {
        fetch(`produtos_handler.php?action=get_details&id=${id}`).then(r => r.json()).then(d => {
            if (d.success) {
                Object.keys(d.product).forEach(key => {
                    const el = form.elements[key];
                    if (el) {
                        if (el.type === 'checkbox') {
                            el.checked = d.product[key] == 1;
                        } else if (el.type === 'radio') {
                            const radio = document.querySelector(`input[name="${key}"][value="${d.product[key]}"]`);
                            if(radio) radio.checked = true;
                        } else {
                            el.value = d.product[key] || '';
                        }
                    }
                });
                renderImageGallery(d.product.images || []);
            } else {
                alert('Erro ao carregar dados do produto.');
            }
        });
    }
    productModal.style.display = 'block';
}
function renderImageGallery(images) {
    const imageGallery = document.getElementById('imageGallery');
    const productId = document.getElementById('productId').value;
    imageGallery.innerHTML = '';
    if (images.length > 0) {
        images.forEach(img => {
            const isCoverClass = img.is_main == 1 ? 'is-cover' : '';
            const titleValue = img.title ? `value="${img.title.replace(/"/g, '&quot;')}"` : '';
            const imageHtml = `
                <div id="image-${img.id}" class="img-thumbnail-container ${isCoverClass}">
                    <img src="../${img.image_path}" alt="Miniatura">
                    <div class="img-actions">
                        <button type="button" onclick="setCoverImage(${img.id}, ${productId})" title="Definir como Capa"><i class="fas fa-star"></i> Capa</button>
                        <button type="button" onclick="deleteImage(${img.id}, this)" title="Excluir Imagem"><i class="fas fa-trash"></i> Excluir</button>
                    </div>
                    <div class="cover-indicator"><i class="fas fa-star fa-xs"></i> CAPA</div>
                    <input type="text" name="image_titles[${img.id}]" ${titleValue} placeholder="Título da imagem" class="image-title-input">
                </div>
            `;
            imageGallery.insertAdjacentHTML('beforeend', imageHtml);
        });
    } else {
        imageGallery.innerHTML = '<div class="p-4 text-center text-gray-500">Este produto ainda não possui imagens.</div>';
    }
}
function renderImagePreviews(files) {
    const imageGallery = document.getElementById('imageGallery');
    if(imageGallery.querySelector('.text-gray-500')){ imageGallery.innerHTML = ''; }
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewId = `preview-${Date.now()}-${Math.random()}`; 
            const previewHtml = `<div id="${previewId}" class="img-thumbnail-container"><img src="${e.target.result}" alt="Pré-visualização"><div class="img-actions" style="justify-content:center;"><span class="text-xs p-2">Aguardando Salvar</span></div><input type="text" placeholder="Título (após salvar)" class="image-title-input" disabled></div>`;
            imageGallery.insertAdjacentHTML('beforeend', previewHtml);
        }
        reader.readAsDataURL(file);
    }
}
function deleteImage(imageId, buttonElement) {
    if (!confirm('Tem certeza que deseja excluir esta imagem?')) return;
    const container = buttonElement.closest('.img-thumbnail-container');
    container.style.opacity = '0.5';
    fetch('produtos_handler.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=delete_image&image_id=${imageId}`})
    .then(r => r.json()).then(d => {
        if (d.success) {
            container.style.transform = 'scale(0)';
            setTimeout(() => container.remove(), 300);
        } else { 
            alert('Erro: ' + d.message); 
            container.style.opacity = '1';
        }
    });
}
function setCoverImage(imageId, productId) {
    fetch('produtos_handler.php', { method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: `action=set_cover&image_id=${imageId}&product_id=${productId}`})
    .then(r => r.json()).then(d => {
        if (d.success) {
            document.querySelectorAll('.img-thumbnail-container').forEach(el => el.classList.remove('is-cover'));
            document.getElementById(`image-${imageId}`).classList.add('is-cover');
        } else { alert('Erro: ' + d.message); }
    });
}
document.getElementById('new_images').addEventListener('change', function(event) {
    const files = event.target.files;
    const productId = document.getElementById('productId').value;
    if (files.length === 0) return;
    if (productId) {
        const formData = new FormData();
        formData.append('action', 'add_images');
        formData.append('product_id', productId);
        for (const file of files) { formData.append('new_images[]', file); }
        const feedback = document.getElementById('upload-feedback');
        const saveBtn = document.getElementById('saveProductBtn');
        feedback.style.display = 'block';
        saveBtn.disabled = true;
        fetch('produtos_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.images) { renderImageGallery(data.images); } 
            else { alert('Erro no upload: ' + (data.message || 'Erro desconhecido.')); }
        })
        .catch(error => alert('Erro de comunicação: ' + error))
        .finally(() => {
            feedback.style.display = 'none';
            saveBtn.disabled = false;
            event.target.value = '';
        });
    } else {
        renderImagePreviews(files);
    }
});
function closeProductModal() { productModal.style.display = 'none'; }
function closePricesModal() {
    pricesModal.style.display = 'none';
    pricesModal.innerHTML = ''; 
}
function openPricesModal(productId, productName) {
    pricesModal.innerHTML = '<div class="modal-content"><div class="p-8 text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Carregando...</p></div></div>';
    pricesModal.style.display = 'block';
    fetch(`produtos_handler.php?action=get_prices&id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Erro ao carregar os preços.');
                closePricesModal();
                return;
            }
            const savedPrices = data.prices || [];
            let formFieldsHTML = '';
            paymentMethodsData.forEach(method => {
                const savedPrice = savedPrices.find(p => p.payment_method_id == method.id);
                const priceValue = savedPrice && savedPrice.price ? parseFloat(savedPrice.price).toLocaleString('pt-BR', { minimumFractionDigits: 2 }).replace(/\./g, '').replace(',', '.') : '';
                const installmentsValue = savedPrice ? savedPrice.installments || '1' : '1';
                const interestRateValue = savedPrice && savedPrice.interest_rate ? parseFloat(savedPrice.interest_rate).toLocaleString('pt-BR', { minimumFractionDigits: 2 }).replace(/\./g, '').replace(',', '.') : '';
                let specificFields = '';
                if (method.name.toLowerCase().includes('crédito')) {
                    const isInterestChecked = savedPrice && savedPrice.interest_rate > 0;
                    const isNoInterestChecked = !isInterestChecked;
                    specificFields = `
                        <div class="form-group border-t pt-4 mt-4">
                            <label class="font-semibold block mb-2">Tipo de Parcelamento</label>
                            <div class="flex gap-4">
                                <div class="flex items-center">
                                    <input type="radio" id="calc_type_no_interest_${method.id}" name="prices[${method.id}][calc_type]" value="no_interest" ${isNoInterestChecked ? 'checked' : ''} onchange="toggleInterestFields(${method.id}, false)">
                                    <label for="calc_type_no_interest_${method.id}" class="ml-2 cursor-pointer">Sem Juros</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" id="calc_type_interest_${method.id}" name="prices[${method.id}][calc_type]" value="interest" ${isInterestChecked ? 'checked' : ''} onchange="toggleInterestFields(${method.id}, true)">
                                    <label for="calc_type_interest_${method.id}" class="ml-2 cursor-pointer">Com Juros</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" id="interest_rate_group_${method.id}" style="${isInterestChecked ? '' : 'display:none;'}">
                            <label>Taxa de Juros Mensal (%)</label>
                            <input type="text" name="prices[${method.id}][interest_rate]" value="${interestRateValue}" class="form-control" placeholder="Ex: 2,99">
                        </div>
                    `;
                }
                formFieldsHTML += `
                    <div class="card mb-4">
                        <div class="card-header">${method.name}</div>
                        <div class="p-4">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Preço ${method.name.toLowerCase().includes('crédito') ? ' (base para cálculo)' : ''}</label>
                                    <input type="text" name="prices[${method.id}][price]" value="${priceValue}" class="form-control" placeholder="Deixe em branco para não usar">
                                </div>
                                <div class="form-group">
                                    <label>Nº de Parcelas</label>
                                    <input type="number" name="prices[${method.id}][installments]" value="${installmentsValue}" class="form-control" placeholder="Ex: 12" min="1">
                                </div>
                            </div>
                            ${specificFields}
                        </div>
                    </div>
                `;
            });
            const modalHTML = `
                <div class="modal-content">
                    <form action="produtos_handler.php" method="POST">
                        <input type="hidden" name="action" value="save_prices">
                        <input type="hidden" name="product_id" value="${productId}">
                        <div class="modal-header">
                            <h5 class="modal-title">Preços Específicos para: <strong>${productName}</strong></h5>
                            <button type="button" class="close-button" onclick="closePricesModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p class="text-sm text-gray-600 mb-4">Defina os preços para cada forma de pagamento. Deixar um preço em branco desativa a forma de pagamento para este item.</p>
                            ${formFieldsHTML}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="bg-gray-200 hover:bg-gray-300 text-black font-bold py-2 px-4 rounded" onclick="closePricesModal()">Cancelar</button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Salvar Preços</button>
                        </div>
                    </form>
                </div>
            `;
            pricesModal.innerHTML = modalHTML;
        })
        .catch(error => {
            console.error('Erro no fetch de preços:', error);
            alert('Ocorreu um erro de comunicação.');
            closePricesModal();
        });
}
function toggleInterestFields(methodId, show) {
    const group = document.getElementById(`interest_rate_group_${methodId}`);
    if (group) {
        group.style.display = show ? 'block' : 'none';
    }
}
window.onclick = e => { 
    if (e.target == productModal) closeProductModal(); 
    if (e.target == pricesModal) closePricesModal(); 
};

// --- @NOVO: Lógica AJAX para os formulários de status ---
document.addEventListener('DOMContentLoaded', function() {
    // Seleciona a tabela para observar novos elementos, se necessário no futuro. Por agora, vamos usar um listener direto.
    const productTableBody = document.querySelector('.table > tbody');

    // Usamos delegação de eventos para capturar o clique no corpo da tabela.
    // Isso é mais eficiente e funciona mesmo se o conteúdo da tabela for atualizado.
    productTableBody.addEventListener('click', function(event) {
        // Encontra o botão de submit que foi clicado, subindo na árvore DOM a partir do alvo do clique.
        const submitButton = event.target.closest('button[type="submit"]');
        
        // Se o clique não foi em um botão de submit dentro de um formulário 'ajax-toggle-form', não faz nada.
        if (!submitButton || !submitButton.parentElement.classList.contains('ajax-toggle-form')) {
            return;
        }

        // Pega o formulário pai do botão.
        const form = submitButton.parentElement;
        
        // Previne o comportamento padrão do formulário (que é recarregar a página).
        // Isso é a correção principal para o seu problema.
        event.preventDefault();

        const formData = new FormData(form);
        const action = formData.get('action');
        const icon = submitButton.querySelector('i');
        const originalIconClass = icon.className;
        
        // Mostra um feedback visual de que algo está acontecendo.
        icon.className = 'fas fa-spinner fa-spin';
        submitButton.disabled = true;

        // Envia os dados para o backend em segundo plano.
        fetch('produtos_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                // Se a resposta do servidor for um erro (como 500), lança uma exceção.
                throw new Error('Erro de servidor: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Se o backend confirmar o sucesso, atualiza a interface.
                const row = form.closest('tr');
                const newState = data.newState; // O novo estado (0 ou 1) que o PHP retornou.

                if (action === 'toggle_active') {
                    updateActiveStatus(row, newState, form);
                } else if (action === 'toggle_sold') {
                    updateSoldStatus(row, newState, form);
                }
            } else {
                // Se o backend retornar um erro ('success': false), exibe a mensagem.
                alert('Erro ao atualizar o status: ' + data.message);
                icon.className = originalIconClass; // Restaura o ícone original em caso de erro.
            }
        })
        .catch(error => {
            // Captura erros de rede ou falhas na comunicação.
            console.error('Erro de Fetch:', error);
            alert('Ocorreu um erro de comunicação com o servidor.');
            icon.className = originalIconClass; // Restaura o ícone original.
        })
        .finally(() => {
            // Executa sempre no final, seja sucesso ou falha.
            submitButton.disabled = false; // Reabilita o botão.
        });
    });
});

// As funções abaixo atualizam a aparência da linha da tabela com base na resposta do servidor.
function updateActiveStatus(row, isActive, form) {
    const statusCell = row.querySelector('.active-status-cell span');
    const button = form.querySelector('button');
    const icon = button.querySelector('i');

    if (isActive) {
        // ATIVADO
        row.classList.remove('opacity-60', 'bg-gray-50');
        statusCell.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
        statusCell.textContent = 'Ativo';
        button.className = 'text-green-500 hover:text-green-700';
        icon.className = 'fas fa-eye';
        form.dataset.tooltip = 'Desativar Anúncio';
    } else {
        // DESATIVADO
        row.classList.add('opacity-60', 'bg-gray-50');
        statusCell.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-800';
        statusCell.textContent = 'Inativo';
        button.className = 'text-gray-400 hover:text-gray-600';
        icon.className = 'fas fa-eye-slash';
        form.dataset.tooltip = 'Ativar Anúncio';
    }
}

function updateSoldStatus(row, isSold, form) {
    const statusCell = row.querySelector('.sold-status-cell span');
    const button = form.querySelector('button');
    const icon = button.querySelector('i');

    if (isSold) {
        // VENDIDO
        statusCell.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800';
        statusCell.textContent = 'Vendido';
        button.className = 'text-green-500 hover:text-green-700';
        icon.className = 'fas fa-toggle-off';
        form.dataset.tooltip = 'Marcar Disponível';
    } else {
        // DISPONÍVEL
        statusCell.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
        statusCell.textContent = 'Disponível';
        button.className = 'text-yellow-500 hover:text-yellow-700';
        icon.className = 'fas fa-toggle-on';
        form.dataset.tooltip = 'Marcar Vendido';
    }
}
</script>

<?php include 'templates/footer.php'; ?>