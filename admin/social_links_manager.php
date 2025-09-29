<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') { die("Acesso negado."); }
include 'templates/header.php';

$social_links = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);

// Lista de ícones pré-selecionados para facilitar
$icons = [
    'fab fa-instagram' => 'Instagram',
    'fab fa-facebook' => 'Facebook',
    'fab fa-whatsapp' => 'WhatsApp',
    'fab fa-youtube' => 'YouTube',
    'fab fa-tiktok' => 'TikTok',
    'fab fa-telegram' => 'Telegram',
    'fab fa-linkedin' => 'LinkedIn',
    'fab fa-twitter' => 'Twitter (X)',
    'fas fa-envelope' => 'E-mail',
];
?>

<h1 class="text-3xl font-bold mb-6">Gerenciar Redes Sociais</h1>

<?php
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 text-green-700 p-4 rounded mb-4">' . sanitize_output($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-4">Adicionar Novo Link</h2>
            <form action="social_links_handler.php" method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div>
                    <label for="icon_class" class="block text-sm font-medium">Ícone</label>
                    <select id="icon_class" name="icon_class" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Selecione um ícone</option>
                        <?php foreach ($icons as $class => $name): ?>
                            <option value="<?php echo $class; ?>"><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="url" class="block text-sm font-medium">URL do Link</label>
                    <input type="url" id="url" name="url" placeholder="https://..." required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <button type="submit" class="brand-bg text-white font-bold py-2 px-6 rounded-md w-full">Adicionar</button>
            </form>
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="bg-white p-6 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-4">Links Atuais</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-2 text-left">Ícone</th>
                            <th class="p-2 text-left">URL</th>
                            <th class="p-2 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($social_links as $link): ?>
                        <tr class="border-t">
                            <td class="p-2"><i class="<?php echo $link['icon_class']; ?> text-2xl"></i></td>
                            <td class="p-2"><a href="<?php echo $link['url']; ?>" target="_blank" class="hover:underline truncate block"><?php echo sanitize_output($link['url']); ?></a></td>
                            <td class="p-2 text-right">
                                <a href="social_links_handler.php?action=delete&id=<?php echo $link['id']; ?>" onclick="return confirm('Tem certeza que deseja remover este link?')" class="text-red-500 hover:text-red-700 font-bold">Remover</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($social_links)): ?>
                            <tr><td colspan="3" class="p-4 text-center text-gray-500">Nenhum link social cadastrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>