<?php
$is_homepage = true; 
require_once 'config/database.php';
require_once 'includes/functions.php';

// --- BUSCA DE CONFIGURAÇÕES E DADOS GERAIS DA PÁGINA ---
$sold_display_days = (int)get_config($pdo, 'sold_display_days', 7);
$logo_path = get_config($pdo, 'logo_path', 'assets/logo.png');

$sold_condition = "";
if ($sold_display_days > 0) {
    $sold_condition = "AND p.sold_timestamp >= DATE_SUB(NOW(), INTERVAL $sold_display_days DAY)";
}

// --- DADOS DAS SEÇÕES (LÓGICA ORIGINAL RESTAURADA) ---
$banners = $pdo->query("SELECT * FROM hero_banners WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$how_it_works_steps = $pdo->query("SELECT * FROM how_it_works WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
$reviews = $pdo->query("SELECT * FROM avaliacoes WHERE status = 'Aprovado' ORDER BY data_avaliacao DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

// Lógica original para buscar os títulos das seções foi restaurada.
$section_titles = [];
$stmt = $pdo->query("SELECT config_key, config_value FROM config_site WHERE config_key LIKE 'section_%'");
while ($row = $stmt->fetch()) {
    $section_titles[$row['config_key']] = $row['config_value'];
}

// --- LÓGICA DE BUSCA DE PRODUTOS (NOVA ESTRUTURA) ---
$common_sql_select = "
    SELECT 
        p.*, 
        (SELECT GROUP_CONCAT(pi.image_path ORDER BY pi.is_main DESC, pi.id ASC SEPARATOR ',') 
         FROM product_images pi 
         WHERE pi.product_id = p.id) as all_images
    FROM products p
";

// 1. Produtos em Destaque
// @ATUALIZADO: Adicionada a condição para buscar apenas produtos ativos.
$featured_products_stmt = $pdo->query("
    $common_sql_select
    WHERE p.is_active = 1 AND p.is_featured = 1 AND p.is_sold = 0
    ORDER BY p.created_at DESC
");
$featured_products = $featured_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Produtos Regulares/Novidades
// @ATUALIZADO: Adicionada a condição para buscar apenas produtos ativos.
$regular_products_stmt = $pdo->query("
    $common_sql_select
    WHERE p.is_active = 1 AND p.is_featured = 0 AND p.is_sold = 0
    ORDER BY p.created_at DESC
");
$regular_products = $regular_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Produtos Vendidos
// @ATUALIZADO: Adicionada a condição para buscar apenas produtos ativos.
$sold_products_stmt = $pdo->query("
    $common_sql_select
    WHERE p.is_active = 1 AND p.is_sold = 1 $sold_condition
    ORDER BY p.sold_timestamp DESC
");
$sold_products = $sold_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. Categorias para o filtro
// @ATUALIZADO: Adicionada a condição para buscar categorias apenas de produtos ativos.
$categories = $pdo->query("SELECT DISTINCT category FROM products WHERE is_active = 1 AND category IS NOT NULL AND category != '' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

// --- FUNÇÃO AUXILIAR PARA RENDERIZAR O CARD DO PRODUTO ---
function render_product_card($product, $card_type = 'regular') {
    $images = !empty($product['all_images']) ? explode(',', $product['all_images']) : [];
    $cover_image = $images[0] ?? 'assets/placeholder.jpg';
    
    $card_class = 'product-card';
    if ($card_type === 'sold') {
        $card_class .= ' sold-product-card';
    }

    $price_html = '';
    if (!empty($product['discount_price']) && $product['discount_price'] > 0) {
        $price_html = '<span class="original-price">R$ ' . number_format($product['price'], 2, ',', '.') . '</span>' .
                      '<span class="discount-price">R$ ' . number_format($product['discount_price'], 2, ',', '.') . '</span>';
    } else {
        $price_html = '<span class="normal-price">R$ ' . number_format($product['price'], 2, ',', '.') . '</span>';
    }

    $badge_html = '';
    if ($product['is_sold']) {
        $badge_html = '<div class="badge badge-vendido">Vendido</div>';
    } elseif ($product['is_featured']) {
        $badge_html = '<div class="badge badge-destaque">Destaque</div>';
    }
    
    $rotation_mode_attr = 'data-rotation-mode="' . htmlspecialchars($product['image_display_mode']) . '"';

    return <<<HTML
    <a href="produto_detalhes.php?id={$product['id']}" 
       class="{$card_class}"
       data-images="{$product['all_images']}"
       data-cover="{$cover_image}"
       {$rotation_mode_attr}>
        <div class="card-image-container">
            {$badge_html}
            <img src="{$cover_image}" alt="{$product['name']}" class="card-image">
        </div>
        <div class="card-content">
            <h3 class="card-title">{$product['name']}</h3>
            <div class="card-price">{$price_html}</div>
        </div>
    </a>
HTML;
}

include 'templates/header.php';
?>

<section class="relative w-full h-screen text-white overflow-hidden">
    <div class="swiper h-full">
        <div class="swiper-wrapper">
            <?php if (empty($banners)): ?>
                <div class="swiper-slide">
                    <div class="absolute inset-0 bg-cover bg-center kenburns-bg" data-bg="https://images.unsplash.com/photo-1593305523953-70423191f636?q=80&w=2070&auto=format&fit=crop"></div>
                    <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,0.28) 0%, rgba(0,0,0,0.45) 40%);"></div>
                    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center p-4">
                        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight drop-shadow-lg">Seu Dispositivo em Mãos de Especialistas</h1>
                        <p class="text-lg md:text-xl mt-4 max-w-2xl drop-shadow-md">Reparos rápidos e com garantia...</p>
                        <a href="solicitar-reparo.php" class="mt-8 bg-white brand-text font-bold py-3 px-8 rounded-full hover:bg-gray-200 transition-transform hover:scale-105 transform inline-block shadow-lg">Solicite um Orçamento Gratuito</a>
                    </div>
                </div>
            <?php else: foreach($banners as $banner): ?>
                <div class="swiper-slide">
                    <div class="absolute inset-0 bg-cover bg-center kenburns-bg" data-bg="<?php echo sanitize_output($banner['image_path']); ?>"></div>
                    <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.45) 45%);"></div>
                    <div class="relative z-10 flex flex-col items-center justify-center h-full text-center p-4">
                        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight drop-shadow-lg"><?php echo sanitize_output($banner['title']); ?></h1>
                        <p class="text-lg md:text-xl mt-4 max-w-2xl drop-shadow-md"><?php echo sanitize_output($banner['subtitle']); ?></p>
                        <a href="solicitar-reparo.php" class="mt-8 bg-white brand-text font-bold py-3 px-8 rounded-full hover:bg-gray-200 transition-transform hover:scale-105 transform inline-block shadow-lg">Solicite um Orçamento Gratuito</a>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="swiper-button-prev" aria-label="Slide anterior"></div>
        <div class="swiper-button-next" aria-label="Próximo slide"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>
<section id="servicos" class="section-frosted with-bottom-wave">
    <div class="container mx-auto px-6 text-center py-24">
        <h2 class="text-3xl md:text-4xl font-bold"><?php echo sanitize_output($section_titles['section_services_title'] ?? 'Nossos Serviços'); ?></h2>
        <p class="mt-4 max-w-2xl mx-auto text-lg opacity-80"><?php echo sanitize_output($section_titles['section_services_subtitle'] ?? 'Confira o que podemos fazer por você.'); ?></p>
        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($services as $service): ?>
            <div class="card p-8 rounded-lg shadow-lg text-left transform hover:-translate-y-2 transition-transform duration-300">
                <div class="service-icon-container inline-block p-4 brand-bg text-white rounded-lg shadow-md">
                    <?php echo $service['icon_svg']; ?>
                </div>
                <h3 class="text-xl font-bold mt-6"><?php echo sanitize_output($service['title']); ?></h3>
                <p class="mt-2 opacity-80"><?php echo sanitize_output($service['description']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="section-clear">
    <div class="container mx-auto px-6 text-center py-24">
        <h2 class="text-3xl md:text-4xl font-bold"><?php echo sanitize_output($section_titles['section_howitworks_title'] ?? 'Como Funciona'); ?></h2>
        <p class="mt-4 max-w-2xl mx-auto text-lg opacity-80"><?php echo sanitize_output($section_titles['section_howitworks_subtitle'] ?? 'Um processo simples em 4 passos.'); ?></p>
        <div class="mt-16 grid grid-cols-1 md:grid-cols-4 gap-x-8 gap-y-12">
             <?php foreach($how_it_works_steps as $step): ?>
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 mb-6 brand-bg text-white rounded-full text-2xl font-bold shadow-lg">
                    <?php echo $step['sort_order']; ?>
                </div>
                <h3 class="text-xl font-bold"><?php echo sanitize_output($step['title']); ?></h3>
                <p class="mt-2 opacity-80"><?php echo sanitize_output($step['description']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="nossos-produtos" class="section-frosted with-top-wave">
    <div class="container mx-auto px-6 text-center py-24">
        <h2 class="text-3xl md:text-4xl font-bold">Nossa Vitrine</h2>
        <p class="mt-4 max-w-2xl mx-auto text-lg opacity-80">Encontre o dispositivo perfeito para você.</p>
        <form id="filter-form" class="filter-bar mt-12 text-left">
            <div class="filter-group">
                <label for="category-filter">Filtrar por Categoria</label>
                <select id="category-filter" name="category" class="filter-select">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label for="price-min-filter">Preço Mínimo</label>
                <input type="number" id="price-min-filter" name="price_min" class="filter-input" placeholder="R$ 0" step="any">
            </div>
            <div class="filter-group">
                <label for="price-max-filter">Preço Máximo</label>
                <input type="number" id="price-max-filter" name="price_max" class="filter-input" placeholder="R$ 5000" step="any">
            </div>
            <div class="filter-group">
                <button type="submit" class="brand-bg text-white hover:bg-opacity-80"><i class="fas fa-search mr-2"></i>Filtrar</button>
            </div>
             <div class="filter-group">
                <button type="reset" id="clear-filters-btn" class="bg-gray-300 text-gray-800 hover:bg-gray-400">Limpar</button>
            </div>
        </form>
        <div id="product-list-container" class="relative min-h-[60vh]">
            <div id="products-skeleton-loader" style="display: none;">
                <img src="<?= htmlspecialchars($logo_path) ?>" alt="Carregando...">
                <p class="text-lg font-semibold mt-4 text-gray-600">Buscando os melhores produtos...</p>
                <div class="skeleton-grid">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="skeleton-card animate-pulse">
                        <div class="skeleton-img"></div>
                        <div class="skeleton-text"></div>
                        <div class="skeleton-text skeleton-text-short"></div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
            <div id="featured-products-section" class="mb-20" <?php if(empty($featured_products)) echo 'style="display:none;"'; ?>>
                <h3 class="text-2xl font-bold text-left mb-6 border-l-4 border-blue-500 pl-4">Destaques</h3>
                <div class="swiper featured-swiper">
                    <div class="swiper-wrapper" id="featured-carousel-wrapper">
                        <?php foreach ($featured_products as $product): ?>
                            <div class="swiper-slide"><?= render_product_card($product) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
            <div id="regular-products-section" class="mb-20">
                <h3 class="text-2xl font-bold text-left mb-6 border-l-4 border-gray-500 pl-4">Novidades</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8" id="regular-products-grid">
                     <?php if (empty($regular_products)): ?>
                        <p class="col-span-full text-gray-500">Nenhum produto novo no momento.</p>
                    <?php else: ?>
                        <?php foreach ($regular_products as $product): echo render_product_card($product); endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div id="sold-products-section" <?php if(empty($sold_products)) echo 'style="display:none;"'; ?>>
                 <h3 class="text-2xl font-bold text-left mb-6 border-l-4 border-red-500 pl-4">Já Vendidos</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6" id="sold-products-grid">
                    <?php foreach ($sold_products as $product): echo render_product_card($product, 'sold'); endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="avaliacoes" class="section-frosted">
    <div class="container mx-auto px-6 text-center py-24">
        <h2 class="text-3xl md:text-4xl font-bold"><?php echo sanitize_output($section_titles['section_reviews_title'] ?? 'O que Nossos Clientes Dizem'); ?></h2>
        <p class="mt-4 max-w-2xl mx-auto text-lg opacity-80"><?php echo sanitize_output($section_titles['section_reviews_subtitle'] ?? 'A satisfação de quem confia em nosso trabalho.'); ?></p>
        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php foreach($reviews as $review): ?>
            <div class="card p-8 rounded-lg shadow-lg">
                <div class="flex justify-center items-center mb-4">
                    <?php for($i = 0; $i < 5; $i++): ?>
                        <svg class="w-5 h-5 <?php echo $i < $review['nota_estrelas'] ? 'text-yellow-400' : 'text-gray-300'; ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <?php endfor; ?>
                </div>
                <p class="opacity-80 italic">"<?php echo sanitize_output($review['comentario']); ?>"</p>
                <p class="mt-6 font-bold text-right">- <?php echo sanitize_output($review['cliente_nome']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const skeletonLoader = document.getElementById('products-skeleton-loader');
    
    const featuredSection = document.getElementById('featured-products-section');
    const regularSection = document.getElementById('regular-products-section');
    const soldSection = document.getElementById('sold-products-section');
    const featuredWrapper = document.getElementById('featured-carousel-wrapper');
    const regularGrid = document.getElementById('regular-products-grid');
    const soldGrid = document.getElementById('sold-products-grid');

    let featuredSwiper = null;

    const createProductCardHTML = (product, cardType = 'regular') => {
        const images = product.all_images ? product.all_images.split(',') : [];
        const coverImage = images[0] || 'assets/placeholder.jpg';
        const cardClass = cardType === 'sold' ? 'product-card sold-product-card' : 'product-card';
        
        let priceHTML = `<span class="normal-price">R$ ${parseFloat(product.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>`;
        if (product.discount_price && parseFloat(product.discount_price) > 0) {
            priceHTML = `<span class="original-price">R$ ${parseFloat(product.price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                         <span class="discount-price">R$ ${parseFloat(product.discount_price).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>`;
        }

        let badgeHTML = '';
        if (product.is_sold == 1) {
            badgeHTML = '<div class="badge badge-vendido">Vendido</div>';
        } else if (product.is_featured == 1) {
            badgeHTML = '<div class="badge badge-destaque">Destaque</div>';
        }

        return `
            <a href="produto_detalhes.php?id=${product.id}" 
               class="${cardClass}"
               data-images="${product.all_images || ''}"
               data-cover="${coverImage}"
               data-rotation-mode="${product.image_display_mode}">
                <div class="card-image-container">
                    ${badgeHTML}
                    <img src="${coverImage}" alt="${product.name}" class="card-image">
                </div>
                <div class="card-content">
                    <h3 class="card-title">${product.name}</h3>
                    <div class="card-price">${priceHTML}</div>
                </div>
            </a>`;
    };

    const renderProducts = (data) => {
        if(featuredWrapper) featuredWrapper.innerHTML = '';
        regularGrid.innerHTML = '';
        if(soldGrid) soldGrid.innerHTML = '';

        if (featuredSection && data.featured && data.featured.length > 0) {
            featuredSection.style.display = 'block';
            data.featured.forEach(product => {
                const slide = document.createElement('div');
                slide.className = 'swiper-slide';
                slide.innerHTML = createProductCardHTML(product);
                featuredWrapper.appendChild(slide);
            });
        } else if(featuredSection) {
            featuredSection.style.display = 'none';
        }

        if (data.regular && data.regular.length > 0) {
            regularSection.style.display = 'block';
            data.regular.forEach(product => {
                regularGrid.innerHTML += createProductCardHTML(product);
            });
        } else {
            regularGrid.innerHTML = '<p class="col-span-full text-gray-500">Nenhum produto encontrado com os filtros selecionados.</p>';
        }

        if (soldSection && data.sold && data.sold.length > 0) {
            soldSection.style.display = 'block';
            data.sold.forEach(product => {
                soldGrid.innerHTML += createProductCardHTML(product, 'sold');
            });
        } else if(soldSection) {
            soldSection.style.display = 'none';
        }
        
        initProductCardFeatures();
    };

    const fetchProducts = async () => {
        skeletonLoader.style.display = 'flex';
        
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData).toString();

        try {
            const response = await fetch(`filter_products_handler.php?${params}`);
            if (!response.ok) throw new Error('A resposta da rede não foi OK');
            const data = await response.json();
            renderProducts(data);
        } catch (error) {
            console.error('Erro ao buscar produtos:', error);
            regularGrid.innerHTML = '<p class="col-span-full text-red-500">Ocorreu um erro ao carregar os produtos. Tente novamente.</p>';
        } finally {
            skeletonLoader.style.display = 'none';
        }
    };

    const initProductCardFeatures = () => {
        if (featuredSwiper) {
            featuredSwiper.destroy(true, true);
            featuredSwiper = null;
        }
        
        if (document.querySelector('.featured-swiper .swiper-slide')) {
            featuredSwiper = new Swiper('.featured-swiper', {
                slidesPerView: 1, spaceBetween: 30, loop: false,
                pagination: { el: '.swiper-pagination', clickable: true },
                breakpoints: { 640: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
            });
        }
        
        document.querySelectorAll('.product-card').forEach(card => {
            const imagePaths = (card.dataset.images || '').split(',').filter(Boolean);
            if (imagePaths.length <= 1) return;

            const cardImage = card.querySelector('.card-image');
            const rotationMode = card.dataset.rotationMode;
            let imageIndex = 0;
            let intervalId = null;

            const startRotation = () => {
                if (intervalId) clearInterval(intervalId);
                intervalId = setInterval(() => {
                    imageIndex = (imageIndex + 1) % imagePaths.length;
                    cardImage.style.opacity = '0';
                    setTimeout(() => {
                        cardImage.src = imagePaths[imageIndex];
                        cardImage.style.opacity = '1';
                    }, 150);
                }, 1200);
            };

            const stopRotation = (returnToCover = true) => {
                clearInterval(intervalId);
                intervalId = null;
                if (returnToCover) {
                    cardImage.style.opacity = '0';
                    setTimeout(() => {
                        cardImage.src = card.dataset.cover;
                        cardImage.style.opacity = '1';
                    }, 150);
                }
            };

            if (rotationMode === 'rotate') {
                startRotation();
                card.addEventListener('mouseenter', () => stopRotation(false));
                card.addEventListener('mouseleave', startRotation);
            } else {
                card.addEventListener('mouseenter', startRotation);
                card.addEventListener('mouseleave', () => stopRotation(true));
            }
        });
    };

    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchProducts();
    });

    clearFiltersBtn.addEventListener('click', (e) => {
        e.preventDefault();
        filterForm.reset();
        fetchProducts(); 
    });

    initProductCardFeatures();
});
</script>

<?php
include 'templates/footer.php';
?>