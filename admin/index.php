<?php
require_once 'check_auth.php';
include 'templates/header.php';

try {
    // --- Buscando dados para os Cards ---
    $novas_solicitacoes = $pdo->query("SELECT COUNT(*) FROM ordens_servico WHERE status = 'Aguardando Chegada'")->fetchColumn();
    $em_andamento_statuses_list = ['Em Análise', 'Aprovado | Em Reparo', 'Aguardando Peças'];
    $em_andamento_placeholders = implode(',', array_fill(0, count($em_andamento_statuses_list), '?'));
    $stmt_em_andamento = $pdo->prepare("SELECT COUNT(*) FROM ordens_servico WHERE status IN ($em_andamento_placeholders)");
    $stmt_em_andamento->execute($em_andamento_statuses_list);
    $em_andamento = $stmt_em_andamento->fetchColumn();
    $aguardando_aprovacao = $pdo->query("SELECT COUNT(*) FROM ordens_servico WHERE status = 'Orçamento Enviado'")->fetchColumn();
    $concluidos_mes = $pdo->query("SELECT COUNT(*) FROM ordens_servico WHERE status IN ('Finalizado', 'Entregue') AND MONTH(data_atualizacao) = MONTH(CURRENT_DATE()) AND YEAR(data_atualizacao) = YEAR(CURRENT_DATE())")->fetchColumn();

    // --- Buscando dados para a Tabela de Últimas OS ---
    $ultimas_os = $pdo->query("SELECT o.id, o.cliente_nome, o.status, o.data_criacao, s.color_bg, s.color_text FROM ordens_servico o LEFT JOIN os_statuses s ON o.status = s.name ORDER BY o.data_criacao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // --- Buscando dados para os GRÁFICOS ---
    $status_counts_stmt = $pdo->query("SELECT status, COUNT(id) as count FROM ordens_servico GROUP BY status");
    $status_counts = $status_counts_stmt->fetchAll(PDO::FETCH_ASSOC);
    $status_colors_stmt = $pdo->query("SELECT name, color_bg FROM os_statuses");
    $status_colors = $status_colors_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $chart_status_labels = []; $chart_status_data = []; $chart_status_colors = [];
    foreach ($status_counts as $row) {
        $chart_status_labels[] = $row['status'];
        $chart_status_data[] = $row['count'];
        $chart_status_colors[] = $status_colors[$row['status']] ?? '#CCCCCC';
    }
    $os_per_month_stmt = $pdo->query("SELECT MONTHNAME(data_criacao) as month, COUNT(id) as count FROM ordens_servico WHERE YEAR(data_criacao) = YEAR(CURRENT_DATE()) GROUP BY MONTH(data_criacao), MONTHNAME(data_criacao) ORDER BY MONTH(data_criacao) ASC");
    $os_per_month = $os_per_month_stmt->fetchAll(PDO::FETCH_ASSOC);
    $chart_monthly_labels = array_column($os_per_month, 'month');
    $chart_monthly_data = array_column($os_per_month, 'count');
} catch (PDOException $e) {
    // Tratamento de erro
    $novas_solicitacoes = $em_andamento = $aguardando_aprovacao = $concluidos_mes = 0;
    $ultimas_os = []; $chart_status_labels = []; $chart_status_data = []; $chart_status_colors = []; $chart_monthly_labels = []; $chart_monthly_data = [];
    echo '<div class="bg-red-200 text-red-800 p-4 rounded">Erro ao carregar dados do dashboard: ' . $e->getMessage() . '</div>';
}
?>

<h1 class="text-3xl font-bold mb-6">Dashboard</h1> 

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <a href="os_list.php?status=Aguardando Chegada" class="block bg-white p-6 rounded-lg shadow-md card transform hover:-translate-y-1 transition-transform">
        <h2 class="text-gray-600 font-bold">Novas Solicitações</h2>
        <p class="text-3xl font-bold brand-text mt-2"><?php echo $novas_solicitacoes ?? 0; ?></p>
    </a>
    <a href="os_list.php?status=<?php echo urlencode(implode(',', $em_andamento_statuses_list)); ?>" class="block bg-white p-6 rounded-lg shadow-md card transform hover:-translate-y-1 transition-transform">
        <h2 class="text-gray-600 font-bold">Serviços em Andamento</h2>
        <p class="text-3xl font-bold brand-text mt-2"><?php echo $em_andamento ?? 0; ?></p>
    </a>
    <a href="os_list.php?status=Orçamento Enviado" class="block bg-white p-6 rounded-lg shadow-md card transform hover:-translate-y-1 transition-transform">
        <h2 class="text-gray-600 font-bold">Aguardando Aprovação</h2>
        <p class="text-3xl font-bold brand-text mt-2"><?php echo $aguardando_aprovacao ?? 0; ?></p>
    </a>
    <div class="bg-white p-6 rounded-lg shadow-md card">
        <h2 class="text-gray-600 font-bold">Concluídos (Mês)</h2>
        <p class="text-3xl font-bold brand-text mt-2"><?php echo $concluidos_mes ?? 0; ?></p>
    </div>
</div>

<div class="mt-8 grid grid-cols-1 lg:grid-cols-5 gap-6">
    <div class="lg:col-span-3 space-y-6">
        <div class="bg-white p-6 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-4">Últimas Ordens de Serviço</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody>
                        <?php if (empty($ultimas_os)): ?>
                            <tr><td class="p-3 text-center text-gray-500">Nenhuma OS recente.</td></tr>
                        <?php else: foreach($ultimas_os as $os): ?>
                        <tr class="border-t">
                            <td class="p-2 font-bold">#<?php echo $os['id']; ?></td>
                            <td class="p-2"><?php echo sanitize_output($os['cliente_nome']); ?></td>
                            <td class="p-2"><span style="background-color:<?php echo $os['color_bg'] ?? '#EEE'; ?>; color:<?php echo $os['color_text'] ?? '#333'; ?>;" class="px-2 py-1 text-xs font-semibold leading-tight rounded-full"><?php echo sanitize_output($os['status']); ?></span></td>
                            <td class="p-2 text-right"><a href="os_details.php?id=<?php echo $os['id']; ?>" class="brand-text font-bold hover:underline">Ver</a></td>
                        </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-4">Volume Mensal de OS (Ano Atual)</h2>
            <canvas id="monthlyOsChart"></canvas>
        </div>
    </div>
    <div class="lg:col-span-2">
        <div class="bg-white p-6 rounded-lg shadow-md card">
            <h2 class="text-xl font-bold mb-4">OS por Status</h2>
            <canvas id="statusPieChart"></canvas>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Lendo as cores do tema diretamente das variáveis CSS para os gráficos
    const computedStyles = getComputedStyle(document.documentElement);
    const primaryColor = computedStyles.getPropertyValue('--cor-primaria').trim();
    const textColor = computedStyles.getPropertyValue('--cor-texto').trim();
    const cardBgColor = computedStyles.getPropertyValue('--cor-fundo-card').trim();
    const gridColor = document.body.classList.contains('dark-theme') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    
    Chart.defaults.color = textColor;
    Chart.defaults.font.family = 'Inter, sans-serif';

    // Gráfico de Pizza: Status
    const statusCtx = document.getElementById('statusPieChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_status_labels ?? []); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_status_data ?? []); ?>,
                    backgroundColor: <?php echo json_encode($chart_status_colors ?? []); ?>,
                    borderColor: cardBgColor,
                    borderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'top', labels: { color: textColor, padding: 20 } } } }
        });
    }

    // Gráfico de Barras: Volume Mensal
    const monthlyCtx = document.getElementById('monthlyOsChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_monthly_labels ?? []); ?>,
                datasets: [{
                    label: 'Nº de Ordens de Serviço',
                    data: <?php echo json_encode($chart_monthly_data ?? []); ?>,
                    backgroundColor: primaryColor + 'BF', // Cor primária com 75% de opacidade
                    borderColor: primaryColor,
                    borderWidth: 2,
                    borderRadius: 5,
                    hoverBackgroundColor: primaryColor
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { color: textColor, precision: 0 }, grid: { color: gridColor } },
                    x: { ticks: { color: textColor }, grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>

<?php include 'templates/footer.php'; ?>