<?php
include 'check_auth.php';
include '../config/database.php';

// Função auxiliar para buscar todas as imagens de um produto
function getProductImages($pdo, $product_id) {
    $stmt = $pdo->prepare("SELECT id, image_path, is_main, title FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Função centralizada para upload de imagens.
 */
function handleImageUploads($pdo, $product_id, $files, $force_first_as_cover = false) {
    if (empty($files['tmp_name'][0])) {
        return; // Nenhum arquivo para enviar
    }

    $upload_dir = '../assets/uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $has_cover = false;
    if (!$force_first_as_cover) {
        $has_cover_stmt = $pdo->prepare("SELECT 1 FROM product_images WHERE product_id = ? AND is_main = 1");
        $has_cover_stmt->execute([$product_id]);
        $has_cover = (bool)$has_cover_stmt->fetchColumn();
    }

    $img_stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, title, is_main) VALUES (?, ?, ?, ?)");

    foreach ($files['tmp_name'] as $key => $tmp) {
        if (!empty($tmp) && $files['error'][$key] == 0) {
            $original_name = basename($files['name'][$key]);
            $safe_name = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $original_name);
            $fname = uniqid('prod_') . '_' . $safe_name;

            if (move_uploaded_file($tmp, $upload_dir . $fname)) {
                $is_main = 0;
                if (($force_first_as_cover && $key == 0) || (!$has_cover && $key == 0)) {
                    $is_main = 1;
                    $has_cover = true;
                }
                
                $initial_title = pathinfo($original_name, PATHINFO_FILENAME);
                $img_stmt->execute([$product_id, 'assets/uploads/products/' . $fname, $initial_title, $is_main]);
            }
        }
    }
}

// Rota para requisições GET (AJAX) - Inalterado
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $action = $_GET['action'] ?? '';

    if ($action === 'get_details' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            $product['images'] = getProductImages($pdo, $_GET['id']);
        }
        echo json_encode(['success' => (bool)$product, 'product' => $product]);
        exit();
    } 
    
    elseif ($action === 'get_prices' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT payment_method_id, price, installments, interest_rate, fixed_installment_value FROM product_payment_prices WHERE product_id = ?");
        $stmt->execute([$_GET['id']]);
        $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'prices' => $prices]);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Ação GET inválida.']);
    exit();
}

// Rota para requisições POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // @NOVO E CORRIGIDO: Bloco para tratar as ações de toggle (Ativo/Vendido) via AJAX
    if ($action === 'toggle_active' || $action === 'toggle_sold') {
        header('Content-Type: application/json');
        try {
            $product_id = $_POST['product_id'];
            // Determina qual coluna do banco de dados será atualizada
            $column = ($action === 'toggle_active') ? 'is_active' : 'is_sold';

            // Pega o estado atual
            $stmt = $pdo->prepare("SELECT $column FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $current_status = $stmt->fetchColumn();
            
            // Inverte o estado (se era 1 vira 0, se era 0 vira 1)
            $new_status = $current_status ? 0 : 1;

            // Atualiza no banco
            $stmt_update = $pdo->prepare("UPDATE products SET $column = ? WHERE id = ?");
            $stmt_update->execute([$new_status, $product_id]);
            
            // Retorna a resposta JSON que o JavaScript está esperando
            echo json_encode(['success' => true, 'newState' => $new_status]);

        } catch (PDOException $e) {
            // Em caso de erro, retorna uma resposta JSON de erro
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit(); // Termina a execução aqui para não cair no redirect abaixo
    }

    // Bloco AJAX para imagens - Inalterado
    if (in_array($action, ['delete_image', 'set_cover', 'add_images'])) {
        header('Content-Type: application/json');
        try {
            $pdo->beginTransaction();
            $product_id = $_POST['product_id'] ?? 0;

            if ($action === 'delete_image') {
                $image_id = $_POST['image_id'];
                $stmt = $pdo->prepare("SELECT image_path, product_id, is_main FROM product_images WHERE id = ?");
                $stmt->execute([$image_id]);
                $image = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($image) {
                    if (file_exists('../' . $image['image_path'])) unlink('../' . $image['image_path']);
                    $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$image_id]);
                    if ($image['is_main']) {
                        $stmt_new = $pdo->prepare("UPDATE product_images SET is_main = 1 WHERE product_id = ? ORDER BY id ASC LIMIT 1");
                        $stmt_new->execute([$image['product_id']]);
                    }
                }
            } elseif ($action === 'set_cover') {
                $image_id = $_POST['image_id'];
                $pdo->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = ?")->execute([$product_id]);
                $pdo->prepare("UPDATE product_images SET is_main = 1 WHERE id = ?")->execute([$image_id]);
            
            } elseif ($action === 'add_images') {
                if ($product_id > 0) {
                    handleImageUploads($pdo, $product_id, $_FILES['new_images'], false);
                }
            }
            $pdo->commit();

            if ($action === 'add_images') {
                echo json_encode(['success' => true, 'images' => getProductImages($pdo, $product_id)]);
            } else {
                echo json_encode(['success' => true]);
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    // Bloco de formulário tradicional (agora sem as ações de toggle)
    try {
        $pdo->beginTransaction();
        if ($action === 'add' || $action === 'edit') {
            
            $fields = [
                $_POST['name'],
                $_POST['description'],
                str_replace(',', '.', $_POST['price']),
                empty($_POST['discount_price']) ? NULL : str_replace(',', '.', $_POST['discount_price']),
                empty($_POST['base_payment_method_id']) ? NULL : $_POST['base_payment_method_id'],
                isset($_POST['is_featured']) ? 1 : 0,
                empty($_POST['category']) ? NULL : trim($_POST['category']),
                $_POST['image_display_mode'] ?? 'static'
            ];
            
            if ($action === 'add') {
                $sql = "INSERT INTO products (name, description, price, discount_price, base_payment_method_id, is_featured, category, image_display_mode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute($fields);
                $product_id = $pdo->lastInsertId();
                handleImageUploads($pdo, $product_id, $_FILES['new_images'], true);

            } else { // edit
                $product_id = $_POST['product_id'];
                $fields[] = $product_id;
                $sql = "UPDATE products SET name=?, description=?, price=?, discount_price=?, base_payment_method_id=?, is_featured=?, category=?, image_display_mode=? WHERE id=?";
                $pdo->prepare($sql)->execute($fields);
            }

            if (isset($_POST['image_titles']) && is_array($_POST['image_titles'])) {
                $update_title_stmt = $pdo->prepare("UPDATE product_images SET title = ? WHERE id = ?");
                foreach ($_POST['image_titles'] as $img_id => $title) {
                    $update_title_stmt->execute([trim($title), $img_id]);
                }
            }

        } 
        
        // Ação de 'delete' continua aqui, pois usa um formulário com reload
        elseif ($action === 'delete') {
            $product_id = $_POST['product_id'];
            $stmt_img = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id=?");
            $stmt_img->execute([$product_id]);
            foreach ($stmt_img->fetchAll(PDO::FETCH_COLUMN) as $img) {
                if (file_exists('../'.$img)) unlink('../'.$img);
            }
            $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$product_id]);
        } 
        
        // @REMOVIDO: A lógica de 'toggle_sold' foi movida para o bloco AJAX acima.
        
        // Ação 'save_prices' continua aqui
        elseif ($action === 'save_prices') {
            $product_id = $_POST['product_id'];
            $pdo->prepare("DELETE FROM product_payment_prices WHERE product_id = ?")->execute([$product_id]);
            $stmt = $pdo->prepare("INSERT INTO product_payment_prices (product_id, payment_method_id, price, installments, interest_rate, fixed_installment_value) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['prices'] ?? [] as $method_id => $data) {
                if (!empty($data['price'])) {
                    $price = str_replace(',', '.', $data['price']);
                    $installments = !empty($data['installments']) ? (int)$data['installments'] : 1;
                    $interest_rate = null;
                    $fixed_installment_value = null;
                    if (isset($data['calc_type'])) {
                        if ($data['calc_type'] === 'interest' && !empty($data['interest_rate'])) $interest_rate = str_replace(',', '.', $data['interest_rate']);
                        elseif ($data['calc_type'] === 'fixed' && !empty($data['fixed_installment_value'])) $fixed_installment_value = str_replace(',', '.', $data['fixed_installment_value']);
                    }
                    $stmt->execute([$product_id, $method_id, $price, $installments, $interest_rate, $fixed_installment_value]);
                }
            }
        }

        $pdo->commit();
        header('Location: produtos_manager.php?status=success&from='.$action);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: produtos_manager.php?status=error&message=' . urlencode($e->getMessage()));
        exit();
    }
}

header('Location: produtos_manager.php');
exit();
?>