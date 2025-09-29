<?php
 $is_homepage = true; 
require_once 'config/database.php';
require_once 'includes/functions.php';

 $banners = $pdo->query("SELECT * FROM hero_banners WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
 $banner_count = count($banners);

// --- BUSCA OS DADOS DAS NOVAS SEÇÕES ---
 $services = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
 $how_it_works_steps = $pdo->query("SELECT * FROM how_it_works WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
 $reviews = $pdo->query("SELECT * FROM avaliacoes WHERE status = 'Aprovado' ORDER BY data_avaliacao DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

// Busca os títulos das seções
 $section_titles = [];
 $stmt = $pdo->query("SELECT config_key, config_value FROM config_site WHERE config_key LIKE 'section_%'");
while ($row = $stmt->fetch()) {
    $section_titles[$row['config_key']] = $row['config_value'];
}
// --- FIM DA BUSCA ---

include 'templates/header.php';
?>

<!-- ======================= BANNER / SLIDER ======================= -->
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

<!-- ======================= SERVIÇOS (primeira seção - Vidro Fosco) ======================= -->
<section id="servicos" class="section-frosted with-bottom-wave">
    <div class="container mx-auto px-6 text-center py-24">
        <h2 class="text-3xl md:text-4xl font-bold"><?php echo sanitize_output($section_titles['section_services_title'] ?? 'Nossos Serviços'); ?></h2>
        <p class="mt-4 max-w-2xl mx-auto text-lg opacity-80"><?php echo sanitize_output($section_titles['section_services_subtitle'] ?? 'Confira o que podemos fazer por você.'); ?></p>

        <div class="mt-16 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($services as $service): ?>
            <div class="card p-8 rounded-lg shadow-lg text-left transform hover:-translate-y-2 transition-transform duration-300">
                <div class="service-icon-container inline-block p-4 brand-bg text-white rounded-lg shadow-md">
                    <?php echo $service['icon_svg']; // O SVG vem do banco ?>
                </div>
                <h3 class="text-xl font-bold mt-6"><?php echo sanitize_output($service['title']); ?></h3>
                <p class="mt-2 opacity-80"><?php echo sanitize_output($service['description']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ======================= COMO FUNCIONA (seção transparente - Janela Nítida) ======================= -->
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

<!-- ======================= AVALIAÇÕES (seção Vidro Fosco) ======================= -->
<section id="avaliacoes" class="section-frosted with-top-wave">
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

<?php
include 'templates/footer.php';
?>