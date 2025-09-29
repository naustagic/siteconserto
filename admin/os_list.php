<?php
require_once 'check_auth.php';
include 'templates/header.php';

// Busca todos os status para o dropdown de filtro
$statuses = $pdo->query("SELECT name FROM os_statuses ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

// --- LÓGICA DE FILTRO CORRIGIDA E APRIMORADA ---
$where_clauses = []; 
$params = [];
if (!empty($_GET['search'])) {
    $where_clauses[] = "(o.cliente_nome LIKE ? OR o.id = ?)";
    $params[] = '%' . $_GET['search'] . '%'; 
    $params[] = $_GET['search'];
}
// CORREÇÃO: Agora aceita múltiplos status separados por vírgula (vindo do Dashboard)
if (!empty($_GET['status'])) {
    $cleaned_statuses = str_replace("'", "", $_GET['status']);
    $status_list = explode(',', $cleaned_statuses);
    
    $placeholders = implode(',', array_fill(0, count($status_list), '?'));
    
    $where_clauses[] = "o.status IN ($placeholders)";
    
    $params = array_merge($params, $status_list);
}
// --- FIM DA CORREÇÃO ---

$sql = "SELECT o.id, o.cliente_nome, o.dispositivo_categoria, o.data_criacao, o.status, s.color_bg, s.color_text 
        FROM ordens_servico o
        LEFT JOIN os_statuses s ON o.status = s.name";

if (!empty($where_clauses)) { 
    $sql .= " WHERE " . implode(' AND ', $where_clauses); 
}
$sql .= " ORDER BY o.data_criacao DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ordens_servico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<h1 class="text-3xl font-bold mb-6">Ordens de Serviço</h1>

<div class="bg-white p-4 rounded-lg shadow-md mb-6 card">
    <form method="GET" action="os_list.php" class="flex flex-col md:flex-row gap-4 items-center">
        <input type="text" name="search" placeholder="Buscar por Nº da OS ou Nome..." value="<?php echo sanitize_output($_GET['search'] ?? ''); ?>" class="w-full md:w-1/3 p-2 border rounded-md">
        <select name="status" class="w-full md:w-1/3 p-2 border rounded-md">
            <option value="">Todos os Status</option>
            <?php foreach ($statuses as $status): ?>
                <option value="<?php echo sanitize_output($status); ?>" <?php echo ($_GET['status'] ?? '') === $status ? 'selected' : ''; ?>><?php echo sanitize_output($status); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="w-full md:w-auto brand-bg text-white font-bold py-2 px-4 rounded-md">Filtrar</button>
        <a href="os_list.php" class="w-full md:w-auto text-center bg-gray-200 text-gray-700 font-bold py-2 px-4 rounded-md">Limpar</a>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-x-auto card">
    <table class="w-full table-auto">
        <thead class="bg-gray-50 border-b-2 border-gray-200">
            <tr>
                <th class="p-3 text-sm font-semibold tracking-wide text-left">OS</th>
                <th class="p-3 text-sm font-semibold tracking-wide text-left">Cliente</th>
                <th class="p-3 text-sm font-semibold tracking-wide text-left">Dispositivo</th>
                <th class="p-3 text-sm font-semibold tracking-wide text-left">Data Abertura</th>
                <th class="p-3 text-sm font-semibold tracking-wide text-left">Status</th>
                <th class="p-3 text-sm font-semibold tracking-wide text-left">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ordens_servico)): ?>
                <tr><td colspan="6" class="p-3 text-center text-gray-500">Nenhuma Ordem de Serviço encontrada.</td></tr>
            <?php else: foreach ($ordens_servico as $os): ?>
                <tr class="bg-white hover:bg-gray-50">
                    <td class="p-3 text-sm text-gray-700 font-bold">#<?php echo $os['id']; ?></td>
                    <td class="p-3 text-sm text-gray-700"><?php echo sanitize_output($os['cliente_nome']); ?></td>
                    <td class="p-3 text-sm text-gray-700"><?php echo sanitize_output($os['dispositivo_categoria']); ?></td>
                    <td class="p-3 text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($os['data_criacao'])); ?></td>
                    <td class="p-3 text-sm"><span style="background-color:<?php echo $os['color_bg'] ?? '#EEE'; ?>; color:<?php echo $os['color_text'] ?? '#333'; ?>;" class="px-2 py-1 font-semibold leading-tight rounded-full"><?php echo sanitize_output($os['status']); ?></span></td>
                    <td class="p-3 text-sm"><a href="os_details.php?id=<?php echo $os['id']; ?>" class="brand-text font-bold hover:underline">Ver Detalhes</a></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php include 'templates/footer.php'; ?>