</main>

<?php
// Busca as redes sociais e outras configs para o rodapé
$social_links = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$brand_name_text = get_config($pdo, 'brand_name_text', 'ReparoPRO');
$facebook_pixel_id = get_config($pdo, 'facebook_pixel_id');
?>

<footer class="site-footer">
    <div class="container mx-auto">
        <?php if (!empty($social_links)): ?>
            <div class="footer-social-links flex justify-center gap-4">
                <?php foreach ($social_links as $link): ?>
                    <a href="<?php echo sanitize_output($link['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo pathinfo($link['icon_class'], PATHINFO_FILENAME); ?>">
                        <i class="<?php echo sanitize_output($link['icon_class']); ?> text-xl"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p class="footer-copyright">
            &copy; <?php echo date('Y'); ?> <?php echo sanitize_output($brand_name_text); ?>. Todos os direitos reservados.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/imask/7.6.0/imask.min.js"></script>

<?php if (!empty($facebook_pixel_id)): ?>
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '<?php echo sanitize_output($facebook_pixel_id); ?>');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=<?php echo sanitize_output($facebook_pixel_id); ?>&ev=PageView&noscript=1"
/></noscript>
<?php endif; // <-- ESTE É O COMANDO QUE FALTAVA ?>

<script>
    // --- Header inteligente (mantido) ---
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('main-header');
        if (header) {
            const scrollCheck = () => {
                if (window.scrollY > 50) header.classList.add('scrolled');
                else header.classList.remove('scrolled');
            };
            window.addEventListener('scroll', scrollCheck);
            scrollCheck();
        }
    });

    // --- Lazy-load background images for slides (data-bg) ---
    (function lazyLoadSlideBackgrounds() {
        const slides = document.querySelectorAll('.kenburns-bg');
        slides.forEach(el => {
            const src = el.getAttribute('data-bg');
            if (!src) return;
            const img = new Image();
            img.onload = () => {
                el.style.backgroundImage = `url('${src}')`;
                el.style.opacity = '1';
            };
            img.onerror = () => {
                el.style.backgroundColor = 'rgba(0,0,0,0.2)';
            };
            setTimeout(()=> img.src = src, Math.random() * 600);
        });
    })();

    // --- Inicialização do Swiper (com lazy e fade) ---
    (function initSwiper() {
        if (!document.querySelector('.swiper')) return;
        const bannerCount = <?php echo ($banner_count ?? 0); ?>;

        const swiper = new Swiper('.swiper', {
            speed: 1500,
            effect: "fade",
            fadeEffect: { crossFade: true },
            autoplay: { delay: 5000, disableOnInteraction: false },
            loop: bannerCount > 1,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            preloadImages: false,
            on: {
                init: function () {
                    if (bannerCount <= 1) {
                        try { this.navigation.destroy(); } catch(e){}
                        try { this.pagination.destroy(); } catch(e){}
                    }
                },
            },
        });
    })();
</script>
</body>
</html>