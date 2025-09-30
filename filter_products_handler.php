<?php
// Define o tipo de conteúdo como JSON para o JavaScript entender a resposta.
header('Content-Type: application/json');

require_once 'config/database.php';
require_once 'includes/functions.php';

try {
    // --- PREPARAÇÃO DOS FILTROS ---
    $where_clauses = [];
    $params = [];

    // Filtro por Categoria
    if (!empty($_GET['category'])) {
        $where_clauses[] = 'p.category = ?';
        $params[] = $_GET['category'];
    }

    // Filtro por Preço Mínimo
    if (!empty($_GET['price_min']) && is_numeric($_GET['price_min'])) {
        $where_clauses[] = 'COALESCE(p.discount_price, p.price) >= ?';
        $params[] = (float)$_GET['price_min'];
    }

    // Filtro por Preço Máximo
    if (!empty($_GET['price_max']) && is_numeric($_GET['price_max'])) {
        $where_clauses[] = 'COALESCE(p.discount_price, p.price) <= ?';
        $params[] = (float)$_GET['price_max'];
    }

    // Monta a string WHERE final para os filtros
    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = ' AND ' . implode(' AND ', $where_clauses);
    }

    // --- EXECUÇÃO DAS CONSULTAS ---
    $sold_display_days = (int)get_config($pdo, 'sold_display_days', 7);
    $sold_condition_sql = "AND p.sold_timestamp >= DATE_SUB(NOW(), INTERVAL $sold_display_days DAY)";
    
    $common_sql_select = "
        SELECT 
            p.*, 
            (SELECT GROUP_CONCAT(pi.image_path ORDER BY pi.is_main DESC, pi.id ASC SEPARATOR ',') 
             FROM product_images pi 
             WHERE pi.product_id = p.id) as all_images
        FROM products p
    ";

    // 1. Produtos em Destaque
    // @ATUALIZADO: Adicionada a condição 'is_active = 1' e corrigida a lógica de destaque vs vendido.
    $featured_sql = "
        $common_sql_select
        WHERE p.is_active = 1 AND p.is_featured = 1 AND p.is_sold = 0
        $where_sql
        ORDER BY p.created_at DESC
    ";
    $stmt_featured = $pdo->prepare($featured_sql);
    $stmt_featured->execute($params);
    $featured_products = $stmt_featured->fetchAll(PDO::FETCH_ASSOC);

    // 2. Produtos Regulares/Novidades
    // @ATUALIZADO: Adicionada a condição 'is_active = 1'.
    $regular_sql = "
        $common_sql_select
        WHERE p.is_active = 1 AND p.is_featured = 0 AND p.is_sold = 0
        $where_sql
        ORDER BY p.created_at DESC
    ";
    $stmt_regular = $pdo->prepare($regular_sql);
    $stmt_regular->execute($params);
    $regular_products = $stmt_regular->fetchAll(PDO::FETCH_ASSOC);

    // 3. Produtos Vendidos
    // @ATUALIZADO: Adicionada a condição 'is_active = 1' e corrigida a lógica de destaque vs vendido.
    $sold_sql = "
        $common_sql_select
        WHERE p.is_active = 1 AND p.is_sold = 1 $sold_condition_sql
        $where_sql
        ORDER BY p.sold_timestamp DESC
    ";
    $stmt_sold = $pdo->prepare($sold_sql);
    $stmt_sold->execute($params);
    $sold_products = $stmt_sold->fetchAll(PDO::FETCH_ASSOC);

    // --- MONTAGEM E ENVIO DA RESPOSTA ---
    $response = [
        'featured' => $featured_products,
        'regular'  => $regular_products,
        'sold'     => $sold_products
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    // Em caso de erro, retorna uma resposta de erro em JSON
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Erro no banco de dados',
        'message' => $e->getMessage()
    ]);
}
?>