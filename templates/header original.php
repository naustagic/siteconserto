<?php
// --- LÓGICA DE TEMA ---
$themes = [
    'default' => [
        '--cor-primaria' => '#2563EB',
        '--cor-fundo' => '#F9FAFB',
        '--cor-texto' => '#1F2937',
        '--cor-fundo-card' => '#FFFFFF',
        '--fundo-card-rgb' => '255, 255, 255'
    ],
    'dark' => [
        '--cor-primaria' => '#60A5FA',
        '--cor-fundo' => '#111827',
        '--cor-texto' => '#F9FAFB',
        '--cor-fundo-card' => '#1F2937',
        '--fundo-card-rgb' => '31, 41, 55'
    ],
    'ocean' => [
        '--cor-primaria' => '#0891B2',
        '--cor-fundo' => '#F0F9FF',
        '--cor-texto' => '#083344',
        '--cor-fundo-card' => '#FFFFFF',
        '--fundo-card-rgb' => '255, 255, 255'
    ],
    'apmidias' => [
        '--cor-primaria' => '#F26522',
        '--cor-fundo' => '#002D9C',
        '--cor-texto' => '#FFFFFF',
        '--cor-fundo-card' => '#0A3A95',
        '--fundo-card-rgb' => '10, 58, 149'
    ],
];
$current_theme_key = get_config($pdo, 'site_theme', 'default');
$current_theme = $themes[$current_theme_key] ?? $themes['default'];

$logo_path = get_config($pdo, 'logo_path');
$logo_light_path = get_config($pdo, 'logo_light_path');
$is_dark_theme = ($current_theme_key === 'dark');
$logo_wrapper_class = !empty($logo_light_path) ? 'has-light-logo' : '';

$body_bg_enabled = get_config($pdo, 'body_bg_enabled', '0');
$body_bg_image_path = get_config($pdo, 'body_bg_image_path', '');
$body_bg_overlay_opacity = get_config($pdo, 'body_bg_overlay_opacity', '0.75');

$site_title = get_config($pdo, 'site_title', 'ReparoPRO');
$brand_name_text = get_config($pdo, 'brand_name_text', '');
$brand_name_color = get_config($pdo, 'brand_name_color', '#FFFFFF');

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo sanitize_output($site_title); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            <?php foreach($current_theme as $key => $value) { echo "$key: $value;"; } ?>
            
            /* Cores para o efeito Glassmorphism */
            --bg-frosted: rgba(var(--fundo-card-rgb), 0.85);
            --bg-clear: rgba(var(--fundo-card-rgb), 0.15);
        }

        <?php if (isset($is_homepage) && $is_homepage && $body_bg_enabled === '1' && !empty($body_bg_image_path)): ?>
        body.is-homepage {
            background-image: url('<?php echo sanitize_output($body_bg_image_path); ?>');
            background-size: cover;
            background-position: center center;
            background-attachment: fixed;
        }
        body.is-homepage::before {
            content: '';
            position: fixed;
            inset: 0;
            background-color: var(--cor-fundo);
            opacity: <?php echo $body_bg_overlay_opacity; ?>;
            z-index: -1;
        }
        <?php endif; ?>

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--cor-fundo); 
            color: var(--cor-texto); 
        }
        
        .brand-bg { background-color: var(--cor-primaria); }
        .brand-text { color: var(--cor-primaria); }
        .card { background-color: var(--cor-fundo-card); }

        body:not(.is-homepage) main {
            padding-top: 5rem;
        }
        @media (min-width: 768px) {
            body:not(.is-homepage) main {
                padding-top: 6rem;
            }
        }

        /* Estilos para o Header com efeito Glassmorphism */
        #main-header {
            position: fixed; 
            width: 100%; 
            z-index: 50; 
            transition: all 0.3s ease-in-out;
            background-color: rgba(var(--fundo-card-rgb), 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dark-theme #main-header {
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }
        
        #main-header .nav-link { 
            color: var(--cor-texto); 
            text-shadow: none; 
        }
        
        #main-header .nav-link:hover { 
            color: var(--cor-primaria); 
        }

        body.is-homepage #main-header:not(.scrolled) {
            background-color: rgba(0, 0, 0, 0.15);
            box-shadow: none;
            border-bottom-color: rgba(255, 255, 255, 0.1);
        }

        body.is-homepage #main-header:not(.scrolled) .nav-link {
            color: white;
            text-shadow: 0 1px 3px rgba(0,0,0,0.5);
        }
        
        body.is-homepage #main-header:not(.scrolled) .nav-link:hover {
            color: #d1d5db; 
        }
        
        .logo-light { opacity: 0; }
        body.is-homepage #main-header:not(.scrolled) .has-light-logo .logo-light { opacity: 1; }
        body.is-homepage #main-header:not(.scrolled) .has-light-logo .logo-default { opacity: 0; }
        
        .brand-name-text {
            font-size: 1.5rem; /* text-2xl */
            font-weight: 800; /* font-extrabold */
            letter-spacing: -0.025em; /* tracking-tight */
            transition: color 0.3s ease-in-out;
        }
        
        body.is-homepage #main-header:not(.scrolled) .brand-name-text {
             text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }
        
        #main-header.scrolled .brand-name-text {
            color: var(--cor-texto) !important; /* !important para sobrepor o estilo inline */
            text-shadow: none;
        }

        /* Estilos para o menu responsivo */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }
        
        .mobile-menu-btn span {
            display: block;
            width: 25px;
            height: 3px;
            margin: 5px 0;
            background-color: var(--cor-texto);
            transition: all 0.3s ease-in-out;
        }
        
        body.is-homepage #main-header:not(.scrolled) .mobile-menu-btn span {
            background-color: white;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
        }
        
        /* Estilos para as seções com efeito Glassmorphism */
        .section-frosted {
            background-color: var(--bg-frosted);
            /* backdrop-filter: blur(15px); <-- REMOVIDO PARA CONSISTÊNCIA VISUAL */
            /* -webkit-backdrop-filter: blur(15px); <-- REMOVIDO PARA CONSISTÊNCIA VISUAL */
            position: relative;
            z-index: 10;
            padding: 60px 0;
        }
        
        .section-clear {
            background-color: var(--bg-clear);
            position: relative;
            z-index: 10;
            padding: 60px 0;
        }

        /* ================================================================ */
        /* INÍCIO DO BLOCO CORRIGIDO PARA AS ONDAS */
        /* ================================================================ */

        .with-bottom-wave {
            padding-bottom: 100px;
            position: relative;
            z-index: 15; 
        }

        .with-bottom-wave::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: 0;
            width: 100%;
            height: 80px;
            background-color: var(--bg-frosted);
            -webkit-mask-image: url('assets/wave/onda_inferior.png');
            mask-image: url('assets/wave/onda_inferior.png');
            -webkit-mask-size: 100% 100%; /* <-- ALTERADO de 'cover' para esticar */
            mask-size: 100% 100%; /* <-- ALTERADO de 'cover' para esticar */
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
            -webkit-mask-position: center bottom;
            mask-position: center bottom;
            z-index: 5;
        }

        .with-top-wave {
            padding-top: 100px;
            position: relative;
            margin-top: -20px;
        }

        .with-top-wave::before {
            content: '';
            position: absolute;
            top: -80px;
            left: 0;
            width: 100%;
            height: 80px;
            background-color: var(--bg-frosted);
            -webkit-mask-image: url('assets/wave/onda_superior.png');
            mask-image: url('assets/wave/onda_superior.png');
            -webkit-mask-size: 100% 100%; /* <-- ALTERADO de 'cover' para esticar */
            mask-size: 100% 100%; /* <-- ALTERADO de 'cover' para esticar */
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
            -webkit-mask-position: center top;
            mask-position: center top;
            z-index: 5;
        }

        /* ================================================================ */
        /* FIM DO BLOCO CORRIGIDO */
        /* ================================================================ */
        
        /* Correção para ícones SVG dentro dos cards de serviços */
        .service-icon-container svg {
            fill: currentColor;
            width: 2.5rem;
            height: 2.5rem;
        }
        
        /* Animação Kenburns para o banner */
        .swiper-slide .kenburns-bg { 
            will-change: transform, opacity; 
            transition: opacity .6s ease; 
            opacity: 0; 
        }
        
        .swiper-slide-active .kenburns-bg { 
            opacity: 1; 
            animation: kenburns 22s ease-in-out infinite alternate; 
        }
        
        @keyframes kenburns { 
            0% { transform: scale(1.07) translate(-1.5%, -1.5%); } 
            100% { transform: scale(1.18) translate(1.5%, 1.5%); } 
        }

        /* Responsividade */
        @media (max-width: 767px) {
            .mobile-menu-btn {
                display: block;
                z-index: 60;
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 70%;
                max-width: 300px;
                height: 100vh;
                background-color: var(--cor-fundo-card);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                flex-direction: column;
                align-items: flex-start;
                padding: 5rem 2rem 2rem;
                transition: right 0.3s ease-in-out;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
                z-index: 55;
            }
            
            .nav-links.active {
                right: 0;
            }
            
            .nav-links a {
                margin: 0.5rem 0;
                font-size: 1.1rem;
            }
            
            .nav-links .brand-bg {
                width: 100%;
                text-align: center;
                margin-top: 1rem;
            }
            
            .with-bottom-wave {
                padding-bottom: 60px;
            }
            
            .with-bottom-wave::after {
                height: 50px;
                bottom: -50px;
            }
            
            .with-top-wave {
                padding-top: 60px;
                margin-top: -10px;
            }
            
            .with-top-wave::before {
                height: 50px;
                top: -50px;
            }
        }

        /* Estilos para inputs em tema escuro */
        .dark-theme input[type="text"],
        .dark-theme input[type="tel"],
        .dark-theme input[type="email"],
        .dark-theme input[type="number"],
        .dark-theme select,
        .dark-theme textarea {
            background-color: #374151 !important; 
            border-color: #4B5563;
            color: var(--cor-texto);
        }
        
        .dark-theme input::placeholder,
        .dark-theme textarea::placeholder {
            color: #9CA3AF;
        }
        
        .dark-theme input:read-only,
        .dark-theme textarea:read-only {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* --- INÍCIO DA ADIÇÃO: Estilos para o Rodapé Sofisticado --- */
        .site-footer {
            background-color: var(--cor-fundo);
            color: var(--cor-texto);
            padding: 4rem 1rem; /* Mais espaçamento vertical */
        }
        
        /* Para temas escuros, o fundo do rodapé é sutilmente diferente do fundo geral */
        .dark-theme .site-footer {
            background-color: #0d1117; /* Um preto um pouco mais profundo que o fundo */
        }
        
        .footer-content {
            display: grid;
            gap: 2rem;
            text-align: center;
            max-width: 1280px; /* container */
            margin-left: auto;
            margin-right: auto;
        }
        
        @media (min-width: 768px) {
            .footer-content {
                grid-template-columns: repeat(3, 1fr);
                text-align: left;
                align-items: center;
            }
        }
        
        .footer-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        @media (min-width: 768px) {
            .footer-brand {
                justify-content: flex-start;
            }
        }
        
        .footer-brand .logo-image {
            height: 2.5rem; /* h-10 */
        }
        
        .footer-brand .brand-text {
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .footer-social-links a {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 9999px;
            color: var(--cor-texto);
            transition: all 0.2s ease-in-out;
            opacity: 0.7;
        }
        
        .footer-social-links a:hover {
            color: var(--cor-primaria);
            transform: translateY(-2px);
            opacity: 1;
        }
        
        .footer-copyright {
            opacity: 0.6;
            font-size: 0.875rem;
            text-align: center;
        }
        
        @media (min-width: 768px) {
            .footer-copyright {
                text-align: right;
            }
        }
        /* --- FIM DA ADIÇÃO --- */
    </style>
</head>
<body class="<?php echo $current_theme_key === 'dark' ? 'dark-theme ' : ''; ?><?php echo isset($is_homepage) && $is_homepage ? 'is-homepage' : ''; ?>">
    <header id="main-header" class="py-4">
        <nav class="container mx-auto px-6 flex justify-between items-center">
            
            <a href="index.php" class="flex items-center gap-3">
                <div class="relative h-10 w-auto <?php echo $logo_wrapper_class; ?>">
                    <?php if (!empty($logo_path)): ?>
                        <img src="<?php echo sanitize_output($logo_path); ?>" alt="Logo ReparoPRO" class="h-10 logo-default transition-opacity duration-300">
                        <?php if (!empty($logo_light_path)): ?>
                            <img src="<?php echo sanitize_output($logo_light_path); ?>" alt="Logo ReparoPRO" class="h-10 logo-light absolute top-0 left-0 transition-opacity duration-300">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($brand_name_text)): ?>
                    <span class="brand-name-text" style="color: <?php echo sanitize_output($brand_name_color); ?>;">
                        <?php echo sanitize_output($brand_name_text); ?>
                    </span>
                <?php endif; ?>
            </a>

            <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menu de navegação">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-links space-x-6 text-sm font-medium" id="nav-links">
                <a href="index.php#servicos" class="nav-link">Serviços</a>
                <a href="solicitar-reparo.php" class="nav-link">Solicitar Reparo</a>
                <a href="acompanhar-os.php" class="nav-link">Acompanhar OS</a>
                <a href="admin/login.php" class="brand-bg text-white px-4 py-2 rounded-md brand-bg-hover transition-all">Painel</a>
            </div>
        </nav>
    </header>
    
    <main>
    
    <script>
        // Script para o menu responsivo
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const navLinks = document.getElementById('nav-links');
            
            mobileMenuBtn.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                
                // Animação do botão hambúrguer
                const spans = mobileMenuBtn.querySelectorAll('span');
                spans[0].style.transform = navLinks.classList.contains('active') ? 'rotate(45deg) translateY(8px)' : '';
                spans[1].style.opacity = navLinks.classList.contains('active') ? '0' : '';
                spans[2].style.transform = navLinks.classList.contains('active') ? 'rotate(-45deg) translateY(-8px)' : '';
            });
            
            // Fechar menu ao clicar em um link
            const links = navLinks.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                    
                    // Resetar animação do botão hambúrguer
                    const spans = mobileMenuBtn.querySelectorAll('span');
                    spans[0].style.transform = '';
                    spans[1].style.opacity = '';
                    spans[2].style.transform = '';
                });
            });
        });
    </script>