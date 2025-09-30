<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Busca a configuração de dias para exibir produtos vendidos.
$sold_display_days = (int)get_config($pdo, 'sold_display_days', 7);
$sold_condition = "";
if ($sold_display_days > 0) {
    // Cria a condição SQL para incluir produtos vendidos recentemente.
    $sold_condition = "OR (p.is_sold = 1 AND p.sold_timestamp >= DATE_SUB(NOW(), INTERVAL $sold_display_days DAY))";
}

// --- LÓGICA DE BUSCA ATUALIZADA ---
// Ambas as consultas agora buscam TODAS as imagens de cada produto usando GROUP_CONCAT.

// 1. Busca produtos em DESTAQUE
$featured_stmt = $pdo->query("
    SELECT 
        p.*, 
        (SELECT GROUP_CONCAT(pi.image_path ORDER BY pi.is_main DESC, pi.id ASC SEPARATOR ',') 
         FROM product_images pi 
         WHERE pi.product_id = p.id) as all_images
    FROM products p
    WHERE p.is_featured = 1 AND (p.is_sold = 0 $sold_condition)
    ORDER BY p.is_sold ASC, p.created_at DESC
");
$featured_products = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Busca produtos REGULARES
$regular_stmt = $pdo->query("
    SELECT 
        p.*, 
        (SELECT GROUP_CONCAT(pi.image_path ORDER BY pi.is_main DESC, pi.id ASC SEPARATOR ',') 
         FROM product_images pi 
         WHERE pi.product_id = p.id) as all_images
    FROM products p
    WHERE p.is_featured = 0 AND (p.is_sold = 0 $sold_condition)
    ORDER BY p.is_sold ASC, p.created_at DESC
");
$regular_products = $regular_stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca os títulos da página
$page_title = get_config($pdo, 'products_page_title', 'Nossa Vitrine de Aparelhos');
$page_subtitle = get_config($pdo, 'products_page_subtitle', 'Confira as melhores oportunidades e aparelhos revisados por especialistas.');

include 'templates/header.php';
?>
<style>
    .product-card .card-image-container {
        position: relative;
        overflow: hidden;
    }
    .badge {
        position: absolute;
        top: 10px;
        right: -35px;
        background-color: var(--cor-primaria);
        color: white;
        padding: 5px 10px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
        width: 150px;
        text-align: center;
        transform: rotate(45deg);
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 10;
    }
    .badge-vendido {
        background-color: #dc2626; /* Vermelho */
    }
    .badge-destaque {
        background-color: #2563eb; /* Azul */
    }
    .card-image {
        transition: opacity 0.3s ease-in-out, transform 0.3s ease;
    }
    .product-card:hover .card-image {
        transform: scale(1.05);
    }
</style>

<section class="section-frosted with-bottom-wave">
    <div class="container mx-auto px-6 text-center py-24">
        <h1 class="text-3xl md:text-4xl font-bold"><?php echo sanitize_output($page_title); ?></h1>
        <p class="mt-4 max-w-2xl mx-auto text-lg opacity-80"><?php echo sanitize_output($page_subtitle); ?></p>

        <?php if (empty($featured_products) && empty($regular_products)): ?>
            <p class="mt-16 text-center text-lg opacity-80">Nenhum produto disponível no momento. Volte em breve!</p>
        <?php else: ?>

            <?php if (!empty($featured_products)): ?>
                <h2 class="text-2xl md:text-3xl font-bold mt-16 text-left">Destaques</h2>
                <div class="swiper featured-swiper mt-8">
                    <div class="swiper-wrapper">
                        <?php foreach ($featured_products as $product): ?>
                            <?php 
                                $images = !empty($product['all_images']) ? explode(',', $product['all_images']) : [];
                                $cover_image = $images[0] ?? 'assets/placeholder.jpg';
                            ?>
                            <div class="swiper-slide">
                                <a href="produto_detalhes.php?id=<?= $product['id'] ?>" 
                                   class="product-card is-featured <?= $product['is_sold'] ? 'is-sold-card' : '' ?>"
                                   data-images="<?= htmlspecialchars(implode(',', $images)) ?>"
                                   data-cover="<?= htmlspecialchars($cover_image) ?>">
                                   
                                    <div class="card-image-container">
                                        <?php if ($product['is_sold']): ?>
                                            <div class="badge badge-vendido">Vendido</div>
                                        <?php elseif ($product['is_featured']): ?>
                                            <div class="badge badge-destaque">Destaque</div>
                                        <?php endif; ?>
                                        <img src="<?= htmlspecialchars($cover_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-image">
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                                        <div class="card-price">
                                            <?php if (!empty($product['discount_price']) && $product['discount_price'] > 0): ?>
                                                <span class="original-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                                <span class="discount-price">R$ <?= number_format($product['discount_price'], 2, ',', '.') ?></span>
                                            <?php else: ?>
                                                <span class="normal-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            <?php endif; ?>

            <?php if (!empty($regular_products)): ?>
                <h2 class="text-2xl md:text-3xl font-bold mt-16 text-left">Mais Oportunidades</h2>
                <div class="products-grid mt-8 text-left">
                    <?php foreach ($regular_products as $product): ?>
                        <?php 
                            $images = !empty($product['all_images']) ? explode(',', $product['all_images']) : [];
                            $cover_image = $images[0] ?? 'assets/placeholder.jpg';
                        ?>
                        <a href="produto_detalhes.php?id=<?= $product['id'] ?>" 
                           class="product-card <?= $product['is_sold'] ? 'is-sold-card' : '' ?>"
                           data-images="<?= htmlspecialchars(implode(',', $images)) ?>"
                           data-cover="<?= htmlspecialchars($cover_image) ?>">
                           
                            <div class="card-image-container">
                                <?php if ($product['is_sold']): ?>
                                    <div class="badge badge-vendido">Vendido</div>
                                <?php endif; ?>
                                <img src="<?= htmlspecialchars($cover_image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-image">
                            </div>
                            <div class="card-content">
                                <h3 class="card-title"><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="card-price">
                                    <?php if (!empty($product['discount_price']) && $product['discount_price'] > 0): ?>
                                        <span class="original-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                        <span class="discount-price">R$ <?= number_format($product['discount_price'], 2, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="normal-price">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializa o Swiper para o carrossel de destaques
        new Swiper('.featured-swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: false,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                640: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 30 },
            }
        });

        // NOVO SCRIPT: Lógica para a galeria de imagens no hover do card
        // Funciona para TODOS os cards da página (destaques e regulares)
        const productCards = document.querySelectorAll('.product-card');

        productCards.forEach(card => {
            const imagePaths = (card.dataset.images || '').split(',').filter(Boolean);
            if (imagePaths.length <= 1) return;

            const cardImage = card.querySelector('.card-image');
            let imageIndex = 0;
            let intervalId = null;

            card.addEventListener('mouseenter', () => {
                if (intervalId) clearInterval(intervalId);

                intervalId = setInterval(() => {
                    imageIndex = (imageIndex + 1) % imagePaths.length;
                    
                    cardImage.style.opacity = '0';
                    setTimeout(() => {
                        cardImage.src = imagePaths[imageIndex];
                        cardImage.style.opacity = '1';
                    }, 150);
                }, 1200);
            });

            card.addEventListener('mouseleave', () => {
                clearInterval(intervalId);
                intervalId = null;
                
                cardImage.style.opacity = '0';
                setTimeout(() => {
                    cardImage.src = card.dataset.cover;
                    cardImage.style.opacity = '1';
                }, 150);
            });
        });
    });
</script>

<?php include 'templates/footer.php'; ?>