<?php
/**
 * Arquivo de Funções Globais
 * Contém funções auxiliares utilizadas em todo o sistema.
 */

/**
 * Sanitize output to prevent XSS attacks.
 *
 * @param string $data The data to be sanitized.
 * @return string The sanitized data.
 */
function sanitize_output($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Fetches a specific configuration value from the database.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $key The configuration key to fetch.
 * @param mixed $default The default value to return if the key is not found.
 * @return mixed The configuration value or the default value.
 */
function get_config($pdo, $key, $default = null) {
    static $config_cache = []; // Cache estático para evitar múltiplas consultas ao BD na mesma requisição

    if (isset($config_cache[$key])) {
        return $config_cache[$key];
    }

    try {
        $stmt = $pdo->prepare("SELECT config_value FROM config_site WHERE config_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();

        if ($result !== false) {
            $config_cache[$key] = $result;
            return $result;
        }
    } catch (PDOException $e) {
        // Em caso de erro, loga e retorna o valor padrão
        error_log("Erro ao buscar configuração '$key': " . $e->getMessage());
        return $default;
    }

    return $default;
}


/**
 * Processa uma imagem enviada, redimensionando e cortando para as dimensões exatas.
 * Garante que a imagem final não fique distorcida, cortando o excesso.
 *
 * @param array $file O array do arquivo de $_FILES.
 * @param string $destination_path O caminho completo para salvar o arquivo final.
 * @param int $target_width A largura final desejada.
 * @param int $target_height A altura final desejada.
 * @return bool True em sucesso, False em falha.
 */
function process_uploaded_image($file, $destination_path, $target_width, $target_height) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    $source_path = $file['tmp_name'];
    
    // Verifica se o arquivo é uma imagem válida
    $image_info = getimagesize($source_path);
    if ($image_info === false) {
        return false;
    }
    
    list($source_width, $source_height, $source_type) = $image_info;

    // Cria a imagem a partir do arquivo de origem
    switch ($source_type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }

    if (!$source_image) {
        return false;
    }

    $source_aspect_ratio = $source_width / $source_height;
    $target_aspect_ratio = $target_width / $target_height;

    $temp_width = $source_width;
    $temp_height = $source_height;
    $x_start = 0;
    $y_start = 0;

    // Lógica de "Crop-to-Fit": Calcula o corte para preencher o alvo sem distorcer
    if ($source_aspect_ratio > $target_aspect_ratio) {
        // A imagem original é mais larga que o alvo (corta as laterais)
        $temp_width = (int) ($source_height * $target_aspect_ratio);
        $x_start = (int) (($source_width - $temp_width) / 2);
    } else {
        // A imagem original é mais alta que o alvo (corta topo/base)
        $temp_height = (int) ($source_width / $target_aspect_ratio);
        $y_start = (int) (($source_height - $temp_height) / 2);
    }
    
    $final_image = imagecreatetruecolor($target_width, $target_height);

    // Preserva a transparência para PNGs
    if ($source_type == IMAGETYPE_PNG) {
        imagealphablending($final_image, false);
        imagesavealpha($final_image, true);
        $transparent = imagecolorallocatealpha($final_image, 255, 255, 255, 127);
        imagefilledrectangle($final_image, 0, 0, $target_width, $target_height, $transparent);
    }

    // Redimensiona e corta a imagem de origem para a imagem final
    imagecopyresampled($final_image, $source_image, 0, 0, $x_start, $y_start, $target_width, $target_height, $temp_width, $temp_height);
    
    // Salva a imagem final como JPEG com 90% de qualidade
    $success = imagejpeg($final_image, $destination_path, 90);

    // Libera a memória
    imagedestroy($source_image);
    imagedestroy($final_image);

    return $success;
}
?>