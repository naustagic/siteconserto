<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Busca o conteúdo HTML salvo no banco de dados
$guia_conteudo = get_config($pdo, 'guia_envio_conteudo', '<p>Nenhuma instrução cadastrada ainda.</p>');

include 'templates/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold brand-text mb-6 pb-4 border-b">Guia de Como Enviar seu Equipamento</h1>
        
        <div class="prose lg:prose-xl max-w-none">
            <?php
                // IMPORTANTE: Aqui não usamos sanitize_output porque queremos renderizar o HTML
                // que o administrador salvou com o editor de texto (negrito, listas, etc.).
                // Como o input vem de um usuário confiável (o admin), o risco é controlado.
                echo $guia_conteudo; 
            ?>
        </div>
    </div>
</div>

<?php
include 'templates/footer.php';
?>