<?php
require_once 'check_auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$os_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$os_id) { die("ID da OS inválido."); }

$stmt = $pdo->prepare("SELECT * FROM ordens_servico WHERE id = ?");
$stmt->execute([$os_id]);
$os = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$os) { die("OS não encontrada."); }

$logo_path = get_config($pdo, 'logo_path');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>OS #<?php echo $os['id']; ?> - ReparoPRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> @media print { body { -webkit-print-color-adjust: exact; } .no-print { display: none; } } </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white p-10 shadow-lg">
        <div class="flex justify-between items-start border-b pb-4">
            <div>
                <?php if ($logo_path): ?><img src="../<?php echo sanitize_output($logo_path); ?>" class="h-12 mb-2"><?php endif; ?>
                <h1 class="text-3xl font-bold">Ordem de Serviço</h1>
                <p class="text-lg font-mono">#<?php echo $os['id']; ?></p>
            </div>
            <div class="text-right">
                <p><strong>ReparoPRO</strong></p>
                <p class="text-sm">Rua Fictícia, 123 - Centro</p>
                <p class="text-sm">contato@reparopro.com</p>
                <p class="text-sm">Data: <?php echo date('d/m/Y'); ?></p>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-8 mt-6">
            <div>
                <h2 class="font-bold text-lg mb-2">Dados do Cliente</h2>
                <p><strong>Nome:</strong> <?php echo sanitize_output($os['cliente_nome']); ?></p>
                <p><strong>WhatsApp:</strong> <?php echo sanitize_output($os['cliente_whatsapp']); ?></p>
                <p><strong>Endereço:</strong> <?php echo sanitize_output($os['rua'] . ', ' . $os['numero'] . ' - ' . $os['cidade']); ?></p>
            </div>
             <div>
                <h2 class="font-bold text-lg mb-2">Dados do Equipamento</h2>
                <p><strong>Dispositivo:</strong> <?php echo sanitize_output($os['dispositivo_categoria']); ?></p>
                <?php if ($os['dispositivo_marca']): ?><p><strong>Marca/Modelo:</strong> <?php echo sanitize_output($os['dispositivo_marca'] . ' / ' . $os['dispositivo_modelo']); ?></p><?php endif; ?>
            </div>
        </div>
        
        <div class="mt-6">
            <h2 class="font-bold text-lg mb-2">Problema Relatado pelo Cliente</h2>
            <div class="p-4 border bg-gray-50 rounded-md min-h-[80px]"><?php echo nl2br(sanitize_output($os['descricao_problema'])); ?></div>
        </div>
        
         <div class="mt-6">
            <h2 class="font-bold text-lg mb-2">Laudo Técnico / Serviços a Executar</h2>
            <div class="p-4 border rounded-md min-h-[120px]"></div>
        </div>
        
         <div class="mt-6 text-right">
            <h2 class="font-bold text-lg">Valor do Orçamento:</h2>
            <p class="text-2xl font-bold"><?php echo $os['valor_orcamento'] ? 'R$ ' . number_format($os['valor_orcamento'], 2, ',', '.') : 'A definir'; ?></p>
        </div>

        <div class="mt-20 grid grid-cols-2 gap-20 text-center">
            <div>
                <p class="border-t pt-2">Assinatura do Cliente</p>
                <p class="text-xs">(Ciente dos termos de serviço)</p>
            </div>
             <div>
                <p class="border-t pt-2">Assinatura do Técnico</p>
            </div>
        </div>
    </div>
    <script> window.onload = function() { window.print(); } </script>
</body>
</html>