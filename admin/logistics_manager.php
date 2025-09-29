<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
include 'templates/header.php';

// Busca dados para as tabelas
$statuses = $pdo->query("SELECT * FROM os_statuses ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$coverage_areas = $pdo->query("SELECT * FROM logistics_coverage ORDER BY state, city ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="text-3xl font-bold mb-6">Logística e Status</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-1 space-y-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Adicionar Novo Status</h2>
            <form action="logistics_handler.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_status">
                <input type="text" name="name" placeholder="Nome do Status" required class="w-full p-2 border rounded-md">
                <div class="flex items-center gap-4">
                    <div><label class="text-sm">Cor do Fundo</label><input type="color" name="color_bg" value="#E5E7EB" class="w-full h-10"></div>
                    <div><label class="text-sm">Cor do Texto</label><input type="color" name="color_text" value="#374151" class="w-full h-10"></div>
                </div>
                <button type="submit" class="w-full brand-bg text-white font-bold py-2 px-4 rounded-md">Adicionar Status</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold mb-4">Adicionar Área de Cobertura</h2>
            <form action="logistics_handler.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_coverage">
                <input type="text" name="city" placeholder="Cidade" required class="w-full p-2 border rounded-md">
                <input type="text" name="state" placeholder="Estado (UF)" maxlength="2" required class="w-full p-2 border rounded-md">
                <input type="number" step="0.01" name="shipping_fee" placeholder="Valor do Frete (R$)" required class="w-full p-2 border rounded-md">
                <label class="flex items-center space-x-2 mt-2">
                    <input type="checkbox" name="allows_pickup" value="1" class="rounded h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                    <span>Permite retirada no balcão?</span>
                </label>
                <button type="submit" class="w-full brand-bg text-white font-bold py-2 px-4 rounded-md">Adicionar Área</button>
            </form>
        </div>
    </div>
    <div class="lg:col-span-2 space-y-8">
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-bold mb-4">Status Cadastrados</h2>
             <div class="space-y-2">
                <?php foreach($statuses as $status): ?>
                <div class="flex justify-between items-center p-2 rounded">
                    <span style="background-color: <?php echo $status['color_bg']; ?>; color: <?php echo $status['color_text']; ?>;" class="px-2 py-1 text-sm font-semibold rounded-full"><?php echo sanitize_output($status['name']); ?></span>
                    <a href="logistics_handler.php?action=delete_status&id=<?php echo $status['id']; ?>" onclick="return confirm('Tem certeza?')" class="text-red-500 hover:underline text-sm">Excluir</a>
                </div>
                <?php endforeach; ?>
             </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-bold mb-4">Áreas de Cobertura / Fretes</h2>
             <table class="w-full text-sm">
                <thead class="text-left font-bold"><tr><th class="p-2">Cidade/UF</th><th class="p-2">Frete</th><th class="p-2">Retirada</th><th class="p-2">Ações</th></tr></thead>
                <tbody>
                    <?php foreach($coverage_areas as $area): ?>
                    <tr class="border-t">
                        <td class="p-2"><?php echo sanitize_output($area['city']); ?> / <?php echo sanitize_output($area['state']); ?></td>
                        <td class="p-2">R$ <?php echo number_format($area['shipping_fee'], 2, ',', '.'); ?></td>
                        <td class="p-2"><?php echo $area['allows_pickup'] ? 'Sim' : 'Não'; ?></td>
                        <td class="p-2"><a href="logistics_handler.php?action=delete_coverage&id=<?php echo $area['id']; ?>" onclick="return confirm('Tem certeza?')" class="text-red-500 hover:underline">Excluir</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
             </table>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>