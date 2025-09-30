<?php
// A sessão é iniciada aqui para controlar o contador de visualizações.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) { 
    header("Location: produtos.php"); 
    exit(); 
}

// --- LÓGICA DO CONTADOR DE VISUALIZAÇÕES ---
// Incrementa o contador no banco apenas uma vez por sessão de usuário.
if (!isset($_SESSION['viewed_products']) || !in_array($product_id, $_SESSION['viewed_products'])) {
    $pdo->prepare("UPDATE products SET view_count = view_count + 1 WHERE id = ?")->execute([$product_id]);
    $_SESSION['viewed_products'][] = $product_id;
}
// --- FIM DO CONTADOR ---

// A consulta do produto foi mantida.
$stmt = $pdo->prepare("
    SELECT p.*, pm.name as base_payment_method_name 
    FROM products p 
    LEFT JOIN payment_methods pm ON p.base_payment_method_id = pm.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { 
    header("Location: produtos.php"); 
    exit(); 
}

// Busca todas as imagens do produto, com a principal sempre em primeiro.
$images = $pdo->query("SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_main DESC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

// A consulta de preços foi mantida.
$prices_stmt = $pdo->prepare("
    SELECT pm.name, pm.logo_path, ppp.price, ppp.installments, ppp.interest_rate, ppp.fixed_installment_value
    FROM product_payment_prices ppp 
    JOIN payment_methods pm ON ppp.payment_method_id = pm.id
    WHERE ppp.product_id = ? AND pm.is_active = 1 ORDER BY pm.sort_order ASC
");
$prices_stmt->execute([$product_id]);
$payment_prices = $prices_stmt->fetchAll(PDO::FETCH_ASSOC);

// CORREÇÃO: Busca a chave 'whatsapp_number' que foi configurada no painel.
$whatsapp_number = get_config($pdo, 'whatsapp_number', '');
$whatsapp_message = urlencode("Olá! Tenho interesse no produto: " . $product['name'] . " (ID: " . $product['id'] . ")");

include 'templates/header.php';
?>
<style>
/* Estilos ATUALIZADOS para a nova galeria e zoom */
.main-image-wrapper { 
    position: relative; 
    overflow: hidden; 
    border-radius: 0.5rem; 
    cursor: zoom-in;
}
#main-product-image {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 0.5rem;
    /* Transições para o efeito de fade e zoom suave */
    transition: opacity 0.3s ease-in-out, transform 0.2s ease;
}
.thumbnail-wrapper img.active {
    border-color: var(--cor-primaria);
    box-shadow: 0 0 8px var(--cor-primaria);
}

/* Outros estilos da página */
.payment-methods-list { list-style: none; padding: 0; margin-top: 1rem; }
.payment-methods-list li { background-color: rgba(var(--fundo-card-rgb), 0.5); border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items-center; }
.payment-methods-list .method-info { display: flex; align-items: center; gap: 1rem; }
.payment-methods-list img { max-height: 35px; max-width: 80px; object-fit: contain; }
.payment-methods-list .method-price { text-align: right; }
.payment-methods-list .price-main { font-size: 1.25rem; font-weight: bold; color: var(--cor-primaria); }
.payment-methods-list .price-installments { font-size: 0.875rem; color: var(--cor-texto); opacity: 0.8; }
.base-price-method { color: var(--cor-texto-contraste); opacity: 0.9; font-weight: 500; font-size: 0.9rem; }
.view-counter { font-size: 0.875rem; opacity: 0.7; }
</style>

<section class="section-frosted">
    <div class="container mx-auto px-6 py-24">
        <div class="mb-8"><a href="produtos.php" class="opacity-70 hover:opacity-100 transition-opacity"><i class="fas fa-arrow-left mr-2"></i>Voltar para a vitrine</a></div>
        
        <div class="product-detail-container">
            <div class="product-gallery">
                <div class="main-image-wrapper" id="main-image-container">
                    <img id="main-product-image" src="<?= htmlspecialchars($images[0]['image_path'] ?? 'https://placehold.co/600x600/e0e0e0/777?text=Sem+Foto') ?>" alt="Imagem principal do produto <?= htmlspecialchars($product['name']) ?>">
                </div>
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-wrapper">
                    <?php foreach($images as $i => $img): ?>
                        <img src="<?= htmlspecialchars($img['image_path']) ?>" 
                             alt="Thumbnail <?= $i+1 ?>" 
                             class="thumbnail-img <?= $i===0 ? 'active' : '' ?>" 
                             data-large-src="<?= htmlspecialchars($img['image_path']) ?>">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
                <h1 class="text-3xl md:text-4xl font-bold"><?= htmlspecialchars($product['name']) ?></h1>
                
                <div class="flex items-center space-x-4 mt-2">
                    <?php if ($product['is_sold']): ?>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">VENDIDO</span>
                    <?php endif; ?>
                    <span class="view-counter"><i class="fas fa-eye mr-1"></i>Visto <?= $product['view_count'] ?> vezes</span>
                </div>
                
                <div class="price-box">
                    <?php if (!empty($product['discount_price']) && $product['discount_price'] > 0): ?>
                        <span class="text-xl line-through" style="color: var(--cor-texto-contraste); opacity: 0.6;">R$ <?= number_format($product['price'], 2, ',', '.') ?></span>
                        <p class="text-4xl font-bold brand-text mt-1">R$ <?= number_format($product['discount_price'], 2, ',', '.') ?></p>
                        <?php if($product['base_payment_method_name']): ?><span class="base-price-method">no <?= htmlspecialchars($product['base_payment_method_name']) ?></span><?php endif; ?>
                    <?php else: ?>
                        <p class="text-4xl font-bold brand-text">R$ <?= number_format($product['price'], 2, ',', '.') ?></p>
                        <?php if($product['base_payment_method_name']): ?><span class="base-price-method">no <?= htmlspecialchars($product['base_payment_method_name']) ?></span><?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($payment_prices) && !$product['is_sold']): ?>
                <div class="mt-6">
                   <h3 class="text-lg font-semibold">Outras formas de pagamento:</h3>
                   <ul class="payment-methods-list">
                        <?php foreach($payment_prices as $p):
                            // Lógica de cálculo de parcelas (inalterada)
                            $total_price = (float)$p['price'];
                            $installments = (int)$p['installments'];
                            $installment_value = $total_price;
                            if ($installments > 1) {
                                if (!empty($p['fixed_installment_value'])) {
                                    $installment_value = (float)$p['fixed_installment_value'];
                                    $total_price = $installment_value * $installments;
                                } elseif (!empty($p['interest_rate'])) {
                                    $interest = (float)$p['interest_rate'];
                                    $total_price = $total_price * (1 + ($interest / 100));
                                    $installment_value = $total_price / $installments;
                                } else {
                                    $installment_value = $total_price / $installments;
                                }
                            }
                        ?>
                        <li>
                            <div class="method-info">
                                <?php if ($p['logo_path']): ?><img src="<?= htmlspecialchars($p['logo_path']) ?>" alt="<?= htmlspecialchars($p['name']) ?>"><?php else: ?><span><?= htmlspecialchars($p['name']) ?></span><?php endif; ?>
                            </div>
                            <div class="method-price">
                                <div class="price-main">R$ <?= number_format($total_price, 2, ',', '.') ?></div>
                                <?php if($installments > 1): ?>
                                    <div class="price-installments">ou em <?= $installments ?>x de R$ <?= number_format($installment_value, 2, ',', '.') ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                   </ul>
                </div>
                <?php endif; ?>

                <h2 class="text-2xl font-semibold mt-8 border-b pb-2">Descrição</h2>
                <div class="prose mt-4 opacity-90 text-justify"><?= nl2br(htmlspecialchars($product['description'])) ?></div>

                <?php if (!$product['is_sold'] && !empty($whatsapp_number)): ?>
                <div class="mt-10">
                    <a href="https://wa.me/<?= $whatsapp_number ?>?text=<?= $whatsapp_message ?>" target="_blank" class="whatsapp-button w-full md:w-auto inline-flex items-center justify-center">
                        <i class="fab fa-whatsapp mr-2"></i> Tenho Interesse!
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const mainImage = document.getElementById('main-product-image');
    const thumbnails = document.querySelectorAll('.thumbnail-img');

    // 1. LÓGICA PARA TROCA DE IMAGEM SUAVE
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const newSrc = this.dataset.largeSrc;
            
            // Ignora o clique se a imagem já for a atual
            if (mainImage.src.endsWith(newSrc)) return;

            // Remove a classe 'active' de todas as miniaturas
            thumbnails.forEach(t => t.classList.remove('active'));
            // Adiciona 'active' à miniatura clicada
            this.classList.add('active');

            // Aplica o efeito de fade-out
            mainImage.style.opacity = '0';

            // Aguarda a transição de fade-out terminar para trocar a imagem
            setTimeout(() => {
                mainImage.src = newSrc;
                // Aplica o efeito de fade-in
                mainImage.style.opacity = '1';
            }, 150); // Metade do tempo da transição CSS
        });
    });

    // 2. LÓGICA PARA O ZOOM SUAVE (LUPA)
    const container = document.getElementById('main-image-container');
    if (container && mainImage) {
        container.addEventListener('mousemove', (e) => {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const xPercent = (x / container.offsetWidth) * 100;
            const yPercent = (y / container.offsetHeight) * 100;

            mainImage.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            mainImage.style.transform = 'scale(2)'; // Aumenta o zoom em 2x
        });

        container.addEventListener('mouseleave', () => {
            // Reseta o zoom quando o mouse sai da imagem
            mainImage.style.transformOrigin = 'center center';
            mainImage.style.transform = 'scale(1)';
        });
    }

});
</script>

<?php include 'templates/footer.php'; ?>