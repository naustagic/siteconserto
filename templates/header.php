<?php
// --- LÓGICA DE TEMA ---
$themes = [
    'default' => [
        '--cor-primaria' => '#2563EB',
        '--cor-fundo' => '#F9FAFB',
        '--cor-texto' => '#1F2937',
        '--cor-fundo-card' => '#FFFFFF',
        '--fundo-card-rgb' => '255, 255, 255',
        // --- VARIÁVEIS DE FORMULÁRIO CORRIGIDAS ---
        '--cor-fundo-input' => '#1E40AF', // Fundo azul escuro, como na imagem
        '--cor-texto-input' => '#FFFFFF', // << CORRIGIDO: Texto branco para contraste
        '--cor-borda-input' => '#2563EB',
        '--cor-placeholder-input' => '#93C5FD', // Placeholder azul claro
        '--cor-foco-input' => '#60A5FA',
    ],
    'dark' => [
        '--cor-primaria' => '#60A5FA',
        '--cor-fundo' => '#111827',
        '--cor-texto' => '#F9FAFB',
        '--cor-fundo-card' => '#1F2937',
        '--fundo-card-rgb' => '31, 41, 55',
        // --- VARIÁVEIS DE FORMULÁRIO (JÁ ESTAVAM CORRETAS) ---
        '--cor-fundo-input' => '#374151',
        '--cor-texto-input' => '#F9FAFB',
        '--cor-borda-input' => '#4B5563',
        '--cor-placeholder-input' => '#9CA3AF',
        '--cor-foco-input' => '#60A5FA',
    ],
    'ocean' => [
        '--cor-primaria' => '#0891B2',
        '--cor-fundo' => '#F0F9FF',
        '--cor-texto' => '#083344',
        '--cor-fundo-card' => '#FFFFFF',
        '--fundo-card-rgb' => '255, 255, 255',
        // --- VARIÁVEIS DE FORMULÁRIO CORRIGIDAS ---
        '--cor-fundo-input' => '#1E40AF', // Fundo azul escuro, como na imagem
        '--cor-texto-input' => '#FFFFFF', // << CORRIGIDO: Texto branco para contraste
        '--cor-borda-input' => '#0891B2',
        '--cor-placeholder-input' => '#93C5FD', // Placeholder azul claro
        '--cor-foco-input' => '#0891B2',
    ],
    'apmidias' => [
        '--cor-primaria' => '#F26522',
        '--cor-fundo' => '#002D9C',
        '--cor-texto' => '#FFFFFF',
        '--cor-fundo-card' => '#0A3A95',
        '--fundo-card-rgb' => '10, 58, 149',
        // --- VARIÁVEIS DE FORMULÁRIO CORRIGIDAS ---
        '--cor-fundo-input' => '#ffffffff', // Fundo azul escuro, como na imagem
        '--cor-texto-input' => '#F26522', // << CORRIGIDO: Texto laranja, como solicitado
        '--cor-borda-input' => '#2563EB',
        '--cor-placeholder-input' => 'rgba(22, 26, 218, 0.6)', // Placeholder laranja mais claro
        '--cor-foco-input' => '#2563EB',
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
    
    <link rel="stylesheet" href="assets/css/style_produtos.css">
    
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
        .card { background-color: var(--cor-fundo-card); border: 1px solid var(--cor-primaria);}
        

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
        .mobile-menu-btn { display: none; background: none; border: none; cursor: pointer; padding: 0.5rem; }
        .mobile-menu-btn span { display: block; width: 25px; height: 3px; margin: 5px 0; background-color: var(--cor-texto); transition: all 0.3s ease-in-out; }
        body.is-homepage #main-header:not(.scrolled) .mobile-menu-btn span { background-color: white; }
        .nav-links { display: flex; align-items: center; }
        
        /* Estilos para as seções com efeito Glassmorphism */
        .section-frosted { background-color: var(--bg-frosted); position: relative; z-index: 10; padding: 60px 0; }
        .section-clear { background-color: var(--bg-clear); position: relative; z-index: 10; padding: 60px 0; }

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

        /* Correção para ícones SVG */
        .service-icon-container svg { fill: currentColor; width: 2.5rem; height: 2.5rem; }
        
        /* Animação Kenburns */
        .swiper-slide .kenburns-bg { will-change: transform, opacity; transition: opacity .6s ease; opacity: 0; }
        .swiper-slide-active .kenburns-bg { opacity: 1; animation: kenburns 22s ease-in-out infinite alternate; }
        @keyframes kenburns { 0% { transform: scale(1.07) translate(-1.5%, -1.5%); } 100% { transform: scale(1.18) translate(1.5%, 1.5%); } }

        /* Responsividade */
        @media (max-width: 767px) {
            .mobile-menu-btn { display: block; z-index: 60; }
            .nav-links { position: fixed; top: 0; right: -100%; width: 70%; max-width: 300px; height: 100vh; background-color: var(--cor-fundo-card); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); flex-direction: column; align-items: flex-start; padding: 5rem 2rem 2rem; transition: right 0.3s ease-in-out; box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1); z-index: 55; }
            .nav-links.active { right: 0; }
            .nav-links a { margin: 0.5rem 0; font-size: 1.1rem; }
            .nav-links .brand-bg { width: 100%; text-align: center; margin-top: 1rem; }
            .with-bottom-wave { padding-bottom: 60px; }
            .with-bottom-wave::after { height: 50px; bottom: -50px; }
            .with-top-wave { padding-top: 60px; margin-top: -10px; }
            .with-top-wave::before { height: 50px; top: -50px; }
        }

/* ================================================================== */
/* --- INÍCIO: SISTEMA DE ESTILOS PARA FORMULÁRIOS (BASEADO EM TEMAS) --- */
/* ================================================================== */

/* 1. Estilo base para todos os campos de formulário */
input[type="text"],
input[type="tel"],
input[type="email"],
input[type="number"],
select,
textarea {
    /* Usa as novas variáveis para definir a aparência */
    background-color: var(--cor-fundo-input) !important; /* sobrepõe utilitários do Tailwind quando necessário */
    color: var(--cor-texto-input);
    border: 1px solid var(--cor-borda-input);
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    border-radius: 0.375rem; /* pequeno raio padrão */
    padding: 0.5rem 0.75rem;
    font-size: 1rem;
    box-sizing: border-box;
}

/* 2. Estilo do placeholder (o texto de dica dentro do campo) */
input::placeholder,
textarea::placeholder {
    color: var(--cor-placeholder-input);
    opacity: 1;
}

/* 3. Efeito de Foco (quando o usuário clica ou navega para o campo) */
input:focus,
select:focus,
textarea:focus,
.brand-ring-focus:focus {
    outline: none;
    border-color: var(--cor-foco-input);
    /* Anel de foco: primeira parte usa variável RGB para um blur sutil, segunda parte usa cor direta */
    box-shadow: 0 0 0 3px rgba(var(--fundo-card-rgb), 0.5), 0 0 0 3px var(--cor-foco-input);
}

/* 4. Estilo para campos que são 'somente leitura' (readonly) */
input[readonly],
textarea[readonly] {
    opacity: 0.7;
    cursor: not-allowed;
}

/* 5. CORREÇÃO FINAL: Garante que a borda de erro vermelha tenha prioridade sobre a borda de foco do tema */
/* (mantido para compatibilidade com classes existentes do Tailwind) */
input.border-red-500:focus,
textarea.border-red-500:focus,
select.border-red-500:focus {
    border-color: #ef4444; /* red-500 */
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
}

/* --- ADIÇÕES: CLASSE DE ERRO ESPECÍFICA E ATRIBUTO DE ACESSIBILIDADE --- */
/* Coloque estas regras depois de todo o CSS base / Tailwind */

/* 6. Classe que o JS deve aplicar ao input quando houver erro */
.input-error,
.input-error:focus {
    border: 2px solid #ef4444 !important;   /* red-500 */
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.18) !important;
    outline: none !important;
    transition: border-color 0.12s ease, box-shadow 0.12s ease !important;
}

/* 7. Suporte por atributo aria-invalid (melhora acessibilidade e permite styling sem classes) */
input[aria-invalid="true"],
textarea[aria-invalid="true"],
select[aria-invalid="true"] {
    border: 2px solid #ef4444 !important;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.14) !important;
}

/* 8. Caso você prefira marcar o container (div) ao invés do input diretamente */
.field-error {
    border: 2px solid #ef4444 !important;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12) !important;
    border-radius: 0.5rem; /* opcional */
    padding: 0.25rem; /* evita que a borda encoste no conteúdo interno */
}

/* 9. Garante comportamento de foco consistente quando o input tem erro */
.input-error.brand-ring-focus:focus,
.input-error:focus,
input[aria-invalid="true"]:focus {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.25) !important;
}

/* 10. Preserva estilo readonly sem confundir com erro */
input[readonly].input-error,
textarea[readonly].input-error {
    cursor: not-allowed;
    opacity: 0.85;
    box-shadow: none !important; /* evita chamar atenção em campos somente leitura */
}

/* 11. Pequenas utilidades opcionais (se quiser mostrar ícone ou espaçamento ao marcar o container) */
.field-error .field-label,
.field-error .field-help {
    color: #991b1b; /* tom mais escuro de vermelho */
}

/* --- ADIÇÕES: CLASSE DE SUCESSO (CAMPO VÁLIDO) --- */

/* 12. Classe que o JS deve aplicar ao input quando estiver válido */
.input-valid,
.input-valid:focus {
    border: 2px solid #10B981 !important; /* green-500 */
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.18) !important;
    outline: none !important;
    transition: border-color 0.12s ease, box-shadow 0.12s ease !important;
}

/* 13. Suporte por atributo aria-invalid para campo válido */
input[aria-invalid="false"],
textarea[aria-invalid="false"],
select[aria-invalid="false"] {
    border: 2px solid #10B981 !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.14) !important;
}

/* 14. Caso prefira marcar o container como válido */
.field-valid {
    border: 2px solid #10B981 !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12) !important;
    border-radius: 0.5rem;
    padding: 0.25rem;
}

/* 15. Comportamento de foco quando válido */
.input-valid.brand-ring-focus:focus,
.input-valid:focus,
input[aria-invalid="false"]:focus {
    border-color: #10B981 !important;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.25) !important;
}

/* 16. Preserva estilo readonly sem confundir com sucesso */
input[readonly].input-valid,
textarea[readonly].input-valid {
    opacity: 0.9;
    box-shadow: none !important;
}

/* 17. Utilitários opcionais para labels quando campo válido */
.field-valid .field-label,
.field-valid .field-help {
    color: #065f46; /* tom escuro de verde */
}

/* --- FIM: SISTEMA DE ESTILOS PARA FORMULÁRIOS --- */
                
        
        /* ================================================================== */
        /* --- INÍCIO: CSS ATUALIZADO DO RODAPÉ E ANIMAÇÃO --- */
        /* ================================================================== */
        
        /* NOVO: O container principal que serve como 'palco' */
        .footer-wrapper {
            position: relative; /* ESSENCIAL: Cria o contexto de posicionamento */
        }

        /* 1. ANIMAÇÃO DO MARIO INTEGRADA AO TEMA */
        .mario-animation-container {
            height: 350px;
            width: 100%;
            position: relative;
            overflow: hidden;
            background-color: var(--cor-fundo);
        }
        
        .mario-animation-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background-color: var(--cor-primaria);
            opacity: 0.10;
            z-index: 5;
            pointer-events: none;
        }

        /* CORRIGIDO: Posicionamento dos personagens principais */
        .mario-top-chars {
            position: absolute;
            top: 0; /* Alinha no topo do .footer-wrapper */
            left: 50%;
            transform: translateX(-50%) translateY(-50%); /* Centraliza na linha */
            height: 140px;
            z-index: 20; /* Garante que fique sobre tudo */
            pointer-events: none; /* Evita que a imagem bloqueie cliques */
        }

        /* Camadas da animação */
        .mario-layer-back, .mario-layer-bushes, .mario-layer-ground {
            position: absolute; left: 0; width: 100%;
            background-repeat: repeat-x;
            animation-name: mario-scroll;
            animation-timing-function: linear;
            animation-iteration-count: infinite;
        }
        .mario-layer-back { bottom: 22px; height: 300px; z-index: 1; background-image: url('assets/footer/img/back.png'); background-size: auto 100%; animation-duration: 25s; }
        .mario-layer-bushes { bottom: 22px; height: 79px; z-index: 3; background-image: url('assets/footer/img/bushes.png'); background-size: auto 100%; animation-duration: 18s; }
        .mario-layer-ground { bottom: 0; height: 22px; z-index: 4; background-image: url('assets/footer/img/ground.png'); background-size: auto 100%; animation-duration: 10s; }
        .mario-char-running { position: absolute; bottom: 22px; width: 50px; height: 50px; background-image: url('assets/footer/img/mario.gif'); background-size: contain; z-index: 3; animation: mario-run-smooth 15s linear infinite; }
        
        /* Responsividade da animação e personagens */
        @media (max-width: 768px) {
            .mario-top-chars { height: 110px; }
            .mario-animation-container { height: 300px; }
        }
        @keyframes mario-scroll { from { background-position-x: 0; } to { background-position-x: -1024px; } }
        @keyframes mario-run-smooth { 0%, 100% { left: 30%; } 50% { left: 50%; } }

        /* 2. RODAPÉ DE CONTEÚDO MODERNO */
        .site-footer-content {
            position: relative;
            background-color: var(--cor-fundo-card);
            color: var(--cor-texto);
            padding: 100px 1rem 2rem 1rem;
            margin-top: -1px;
        }
        .dark-theme .site-footer-content { background-color: #0d1117; }

        /* A ONDA que conecta a animação ao rodapé */
        .site-footer-content::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            width: 100%;
            height: 80px;
            background-color: var(--cor-fundo-card);
            -webkit-mask-image: url('assets/wave/onda_superior.png');
            mask-image: url('assets/wave/onda_superior.png');
            -webkit-mask-size: 100% 100%;
            mask-size: 100% 100%;
            -webkit-mask-repeat: no-repeat;
            mask-repeat: no-repeat;
        }
        .dark-theme .site-footer-content::before { background-color: #0d1117; }

        /* Estilos do grid e conteúdo do rodapé (mantidos como estavam) */
        .footer-grid { display: grid; grid-template-columns: 1fr; gap: 2.5rem; text-align: center; max-width: 1280px; margin: 0 auto; }
        @media (min-width: 768px) { .footer-grid { grid-template-columns: 2fr 1fr 1fr; text-align: left; align-items: start; } }
        .footer-brand .logo { height: 2.5rem; }
        .footer-brand .brand-name { font-size: 1.5rem; font-weight: 800; color: var(--cor-primaria); }
        .footer-brand p { opacity: 0.7; margin-top: 0.5rem; }
        .footer-links-column h3 { font-weight: 700; font-size: 1rem; color: var(--cor-primaria); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .footer-links-column a { display: block; margin-bottom: 0.5rem; opacity: 0.7; transition: all 0.2s ease; }
        .footer-links-column a:hover { opacity: 1; color: var(--cor-primaria); padding-left: 5px; }
        .footer-social-icons a { display: inline-flex; justify-content: center; align-items: center; width: 2.5rem; height: 2.5rem; border: 1px solid rgba(128, 128, 128, 0.2); border-radius: 50%; color: var(--cor-texto); opacity: 0.7; transition: all 0.2s ease; }
        .footer-social-icons a:hover { opacity: 1; color: white; background-color: var(--cor-primaria); border-color: var(--cor-primaria); transform: translateY(-3px) scale(1.05); }
        .footer-copyright { max-width: 1280px; margin: 0 auto; margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid rgba(128, 128, 128, 0.2); text-align: center; opacity: 0.6; font-size: 0.875rem; }

        /* ================================================================== */
        /* --- FIM: CSS ATUALIZADO --- */
        /* ================================================================== */
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
                <a href="produtos.php" class="nav-link">Produtos</a>
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