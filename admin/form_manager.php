<?php
require_once 'check_auth.php';
if ($_SESSION['user_level'] !== 'Admin') {
    die("Acesso negado.");
}
include 'templates/header.php';

// --- LÓGICA DE PREPARAÇÃO ---

// Determina se estamos em modo de edição ou adição
$edit_mode = false;
$editing_option = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM form_options WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $editing_option = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Busca todas as opções para a árvore e para os seletores
$all_options_stmt = $pdo->query("SELECT * FROM form_options ORDER BY `parent_id` ASC, `type` ASC, `name` ASC");
$all_options = $all_options_stmt->fetchAll(PDO::FETCH_ASSOC);

// Constrói a árvore de opções para exibição
$options_tree = [];
$lookup = [];
foreach ($all_options as $option) {
    $lookup[$option['id']] = $option;
    $lookup[$option['id']]['children'] = [];
    $lookup[$option['id']]['depth'] = 0; // Adiciona profundidade para indentação
}
foreach ($lookup as $id => &$node) {
    if ($node['parent_id'] !== null && isset($lookup[$node['parent_id']])) {
        $lookup[$node['parent_id']]['children'][] =& $node;
        $node['depth'] = $lookup[$node['parent_id']]['depth'] + 1;
    }
}
foreach ($lookup as $id => &$node) {
    if ($node['parent_id'] === null) {
        $options_tree[] =& $node;
    }
}
unset($node);

// Função recursiva para exibir a árvore de forma moderna
function display_tree_modern($nodes) {
    echo '<div class="space-y-2">';
    foreach ($nodes as $node) {
        $indent_class = 'ml-' . ($node['depth'] * 6);
        $type_colors = [
            'CATEGORY' => 'bg-gray-200 text-gray-800',
            'SUBCATEGORY' => 'bg-blue-100 text-blue-800',
            'COMMON_PROBLEM' => 'bg-green-100 text-green-800'
        ];
        
        echo '<div class="flex items-center ' . $indent_class . '">';
        echo '  <div class="flex-grow flex items-center bg-white p-2 rounded-lg shadow-sm border border-gray-200">';
        echo '      <span class="font-mono text-xs px-2 py-1 rounded-md mr-3 ' . $type_colors[$node['type']] . '">' . substr($node['type'], 0, 1) . '</span>';
        if($node['icon_path']) echo '<img src="../' . sanitize_output($node['icon_path']) . '" class="h-6 w-6 mr-2 object-contain">';
        echo '      <span class="font-semibold">' . sanitize_output($node['name']) . '</span>';
        echo '  </div>';
        echo '  <div class="ml-4 flex-shrink-0 space-x-3">';
        echo '      <a href="form_manager.php?action=edit&id=' . $node['id'] . '" class="text-blue-600 hover:text-blue-800 font-medium">Editar</a>';
        $confirm_msg = addslashes($node['type'] === 'CATEGORY' ? 'Tem certeza? Isso apagará TODAS as sub-categorias e problemas associados!' : 'Tem certeza?');
        echo '      <a href="form_manager_handler.php?action=delete&id=' . $node['id'] . '" onclick="return confirm(\'' . $confirm_msg . '\')" class="text-red-600 hover:text-red-800 font-medium">Excluir</a>';
        echo '  </div>';
        echo '</div>';
        
        if (!empty($node['children'])) {
            display_tree_modern($node['children']);
        }
    }
    echo '</div>';
}

?>

<h1 class="text-3xl font-bold mb-6">Gestor de Opções do Formulário</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-md sticky top-8">
            <h2 class="text-xl font-bold mb-4"><?php echo $edit_mode ? 'Editando Opção' : 'Adicionar Nova Opção'; ?></h2>
            <form action="form_manager_handler.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'edit' : 'add'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo $editing_option['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Tipo de Opção</label>
                    <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" <?php echo $edit_mode ? 'disabled' : ''; ?>>
                        <option value="CATEGORY" <?php echo ($editing_option['type'] ?? '') === 'CATEGORY' ? 'selected' : ''; ?>>Categoria Principal</option>
                        <option value="SUBCATEGORY" <?php echo ($editing_option['type'] ?? '') === 'SUBCATEGORY' ? 'selected' : ''; ?>>Sub-Categoria (ex: PS5)</option>
                        <option value="COMMON_PROBLEM" <?php echo ($editing_option['type'] ?? '') === 'COMMON_PROBLEM' ? 'selected' : ''; ?>>Problema Comum</option>
                    </select>
                     <?php if ($edit_mode): ?><input type="hidden" name="type" value="<?php echo $editing_option['type']; ?>"><?php endif; ?>
                </div>
                <div>
                    <label for="parent_id" class="block text-sm font-medium text-gray-700">Associar a (Pai)</label>
                    <select id="parent_id" name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Nenhum (se for Categoria Principal)</option>
                        <?php foreach($all_options as $opt): if($opt['type'] != 'COMMON_PROBLEM' && $opt['id'] != ($editing_option['id'] ?? '')): ?>
                            <option value="<?php echo $opt['id']; ?>" <?php echo (($editing_option['parent_id'] ?? '') == $opt['id']) ? 'selected' : ''; ?>>
                                <?php echo str_repeat('&nbsp;&nbsp;&nbsp;', $lookup[$opt['id']]['depth']) . '↳ ' . sanitize_output($opt['name']); ?>
                            </option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                 <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome da Opção</label>
                    <input type="text" id="name" name="name" value="<?php echo sanitize_output($editing_option['name'] ?? ''); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                
                <div id="icon-field" class="<?php echo ($editing_option['type'] ?? '') !== 'SUBCATEGORY' ? 'hidden' : ''; ?>">
                    <label for="icon" class="block text-sm font-medium text-gray-700">Ícone (PNG, 64x64, opcional)</label>
                    <input type="file" name="icon" id="icon" class="mt-1 block w-full text-sm">
                    <?php if($edit_mode && $editing_option['icon_path']): ?>
                        <img src="../<?php echo sanitize_output($editing_option['icon_path']);?>" class="inline h-10 w-10 mt-2 p-1 border rounded">
                    <?php endif; ?>
                </div>
                <div id="brand-model-field" class="<?php echo ($editing_option['type'] ?? 'CATEGORY') !== 'CATEGORY' ? 'hidden' : ''; ?>">
                     <label class="flex items-center space-x-2">
                        <input type="checkbox" name="requires_brand_model" value="1" <?php echo !empty($editing_option['requires_brand_model']) ? 'checked' : ''; ?> class="rounded h-4 w-4 text-indigo-600 focus:ring-indigo-500">
                        <span>Exigir Marca/Modelo para esta categoria?</span>
                    </label>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full brand-bg text-white font-bold py-2 px-4 rounded-md hover:filter hover:brightness-110 transition-all"><?php echo $edit_mode ? 'Salvar Alterações' : 'Adicionar Opção'; ?></button>
                    <?php if ($edit_mode): ?>
                        <a href="form_manager.php" class="block text-center mt-2 text-sm text-gray-600 hover:underline">Cancelar Edição</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
         <div class="bg-white p-6 rounded-lg shadow-md">
             <h2 class="text-xl font-bold mb-4">Estrutura Atual de Opções</h2>
             <?php if (empty($options_tree)): ?>
                <p class="text-gray-500">Nenhuma opção cadastrada ainda. Comece adicionando uma "Categoria Principal" ao lado.</p>
             <?php else: ?>
                <?php display_tree_modern($options_tree); ?>
             <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Mostra/esconde campos dinâmicos no formulário de adição
document.getElementById('type').addEventListener('change', function() {
    const iconField = document.getElementById('icon-field');
    const brandModelField = document.getElementById('brand-model-field');
    iconField.classList.toggle('hidden', this.value !== 'SUBCATEGORY');
    brandModelField.classList.toggle('hidden', this.value !== 'CATEGORY');
});
</script>

<?php include 'templates/footer.php'; ?>