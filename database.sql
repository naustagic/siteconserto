/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : conserto

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 29/09/2025 14:49:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for avaliacoes
-- ----------------------------
DROP TABLE IF EXISTS `avaliacoes`;
CREATE TABLE `avaliacoes`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `os_id` int NOT NULL,
  `cliente_nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nota_estrelas` tinyint(1) NOT NULL,
  `comentario` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `status` enum('Pendente','Aprovado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendente',
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `os_id`(`os_id` ASC) USING BTREE,
  CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of avaliacoes
-- ----------------------------

-- ----------------------------
-- Table structure for config_site
-- ----------------------------
DROP TABLE IF EXISTS `config_site`;
CREATE TABLE `config_site`  (
  `config_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `config_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`config_key`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of config_site
-- ----------------------------
INSERT INTO `config_site` VALUES ('area_abrangencia', '{\"cidades\":[\"São Paulo\",\"Guarulhos\",\"Osasco\"],\"estados\":[\"SP\"]}');
INSERT INTO `config_site` VALUES ('body_bg_enabled', '1');
INSERT INTO `config_site` VALUES ('body_bg_image_path', 'assets/uploads/backgrounds/bg-1759035761.jpg');
INSERT INTO `config_site` VALUES ('body_bg_overlay_opacity', '0.8');
INSERT INTO `config_site` VALUES ('brand_name_color', '#f26522');
INSERT INTO `config_site` VALUES ('brand_name_text', 'ApMidias');
INSERT INTO `config_site` VALUES ('facebook_pixel_id', '');
INSERT INTO `config_site` VALUES ('guia_envio_conteudo', '<p>Aqui v&atilde;o as instru&ccedil;&otilde;es detalhadas de como embalar e enviar seu equipamento com seguran&ccedil;a. Edite este texto no painel administrativo!</p>');
INSERT INTO `config_site` VALUES ('logo_path', 'assets/uploads/logo/logo.jpg');
INSERT INTO `config_site` VALUES ('section_howitworks_subtitle', 'Nosso processo é simples, rápido e transparente.');
INSERT INTO `config_site` VALUES ('section_howitworks_title', 'Como Funciona');
INSERT INTO `config_site` VALUES ('section_reviews_subtitle', 'Confiança e qualidade que geram resultados.');
INSERT INTO `config_site` VALUES ('section_reviews_title', 'O que Nossos Clientes Dizem');
INSERT INTO `config_site` VALUES ('section_services_subtitle', 'Soluções completas para todos os seus dispositivos.');
INSERT INTO `config_site` VALUES ('section_services_title', 'Serviços Especializados');
INSERT INTO `config_site` VALUES ('site_theme', 'apmidias');
INSERT INTO `config_site` VALUES ('site_title', 'ApMidias- Sua Central de Reparos');
INSERT INTO `config_site` VALUES ('telegram_chat_id', '');
INSERT INTO `config_site` VALUES ('telegram_token', '');

-- ----------------------------
-- Table structure for form_options
-- ----------------------------
DROP TABLE IF EXISTS `form_options`;
CREATE TABLE `form_options`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NULL DEFAULT NULL,
  `type` enum('CATEGORY','SUBCATEGORY','COMMON_PROBLEM') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `requires_brand_model` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `parent_id`(`parent_id` ASC) USING BTREE,
  CONSTRAINT `form_options_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `form_options` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of form_options
-- ----------------------------
INSERT INTO `form_options` VALUES (1, NULL, 'CATEGORY', 'Console', NULL, 0);
INSERT INTO `form_options` VALUES (2, NULL, 'CATEGORY', 'PC Gamer', NULL, 1);
INSERT INTO `form_options` VALUES (3, NULL, 'CATEGORY', 'Notebook', NULL, 1);
INSERT INTO `form_options` VALUES (4, NULL, 'CATEGORY', 'Celular', NULL, 1);
INSERT INTO `form_options` VALUES (5, 1, 'SUBCATEGORY', 'PlayStation 5', 'assets/img/icons/ps5.png', 0);
INSERT INTO `form_options` VALUES (6, 1, 'SUBCATEGORY', 'PlayStation 4', 'assets/img/icons/ps4.png', 0);
INSERT INTO `form_options` VALUES (7, 1, 'SUBCATEGORY', 'Xbox Series S/X', 'assets/img/icons/xbox.png', 0);
INSERT INTO `form_options` VALUES (8, 1, 'SUBCATEGORY', 'Nintendo Switch', 'assets/img/icons/switch.png', 0);
INSERT INTO `form_options` VALUES (9, 5, 'COMMON_PROBLEM', 'Não liga (Luz Azul)', NULL, 0);
INSERT INTO `form_options` VALUES (10, 5, 'COMMON_PROBLEM', 'Erro de leitura de disco', NULL, 0);
INSERT INTO `form_options` VALUES (11, 5, 'COMMON_PROBLEM', 'Superaquecimento', NULL, 0);
INSERT INTO `form_options` VALUES (12, 2, 'COMMON_PROBLEM', 'Não dá vídeo', NULL, 0);
INSERT INTO `form_options` VALUES (13, 2, 'COMMON_PROBLEM', 'Reiniciando sozinho', NULL, 0);
INSERT INTO `form_options` VALUES (14, 2, 'COMMON_PROBLEM', 'Upgrade de Placa de Vídeo', NULL, 0);
INSERT INTO `form_options` VALUES (15, 4, 'COMMON_PROBLEM', 'Tela quebrada', NULL, 0);
INSERT INTO `form_options` VALUES (16, 4, 'COMMON_PROBLEM', 'Troca de Bateria', NULL, 0);

-- ----------------------------
-- Table structure for hero_banners
-- ----------------------------
DROP TABLE IF EXISTS `hero_banners`;
CREATE TABLE `hero_banners`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `subtitle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of hero_banners
-- ----------------------------
INSERT INTO `hero_banners` VALUES (15, 'assets/uploads/banners/banner_68d868dc6db2f.jpg', 'Seu Dispositivo em Mãos de Especialistas', 'Reparos rápidos e com garantia para Consoles, PCs, Notebooks, Celulares e mais.', 0, 1);
INSERT INTO `hero_banners` VALUES (16, 'assets/uploads/banners/banner_68d868dcb4c82.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (17, 'assets/uploads/banners/banner_68d868dd662d1.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (18, 'assets/uploads/banners/banner_68d868de53a0a.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (19, 'assets/uploads/banners/banner_68d868deb1fd9.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (20, 'assets/uploads/banners/banner_68d868df65017.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (21, 'assets/uploads/banners/banner_68d868dfab1d3.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (22, 'assets/uploads/banners/banner_68d868e00a441.jpg', '', '', 0, 1);
INSERT INTO `hero_banners` VALUES (23, 'assets/uploads/banners/banner_68d868e0569e9.jpg', '', '', 0, 1);

-- ----------------------------
-- Table structure for how_it_works
-- ----------------------------
DROP TABLE IF EXISTS `how_it_works`;
CREATE TABLE `how_it_works`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon_svg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of how_it_works
-- ----------------------------
INSERT INTO `how_it_works` VALUES (1, '<svg>...</svg>', '1. Solicite Online', 'Preencha nosso formulário inteligente em menos de 2 minutos descrevendo seu dispositivo e o problema.', 1, 1);
INSERT INTO `how_it_works` VALUES (2, '<svg>...</svg>', '2. Envie o Aparelho', 'Embale seu dispositivo com segurança e envie-o para nós ou utilize nosso serviço de coleta (se disponível).', 2, 1);
INSERT INTO `how_it_works` VALUES (3, '<svg>...</svg>', '3. Orçamento e Reparo', 'Nossos técnicos analisam, enviam o orçamento para sua aprovação e realizam o reparo com peças de qualidade.', 3, 1);
INSERT INTO `how_it_works` VALUES (4, '<svg>...</svg>', '4. Receba de Volta', 'Após o reparo e testes, enviamos seu dispositivo de volta para você, pronto para a ação!', 4, 1);

-- ----------------------------
-- Table structure for logistics_coverage
-- ----------------------------
DROP TABLE IF EXISTS `logistics_coverage`;
CREATE TABLE `logistics_coverage`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_fee` decimal(10, 2) NOT NULL DEFAULT 0.00,
  `allows_pickup` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of logistics_coverage
-- ----------------------------
INSERT INTO `logistics_coverage` VALUES (1, 'São Paulo', 'SP', 25.00, 0);
INSERT INTO `logistics_coverage` VALUES (2, 'Guarulhos', 'SP', 35.50, 0);
INSERT INTO `logistics_coverage` VALUES (3, 'Capim Grosso', 'BA', 0.00, 0);

-- ----------------------------
-- Table structure for ordens_servico
-- ----------------------------
DROP TABLE IF EXISTS `ordens_servico`;
CREATE TABLE `ordens_servico`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cliente_whatsapp` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cliente_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `cep` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rua` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `numero` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `bairro` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cidade` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `estado` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dispositivo_categoria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `dispositivo_marca` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `dispositivo_modelo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `problemas_selecionados` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `descricao_problema` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `media_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Aguardando Chegada',
  `codigo_rastreio_devolucao` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `valor_orcamento` decimal(10, 2) NULL DEFAULT NULL,
  `orcamento_status` enum('Pendente','Aprovado','Recusado') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp,
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ordens_servico
-- ----------------------------
INSERT INTO `ordens_servico` VALUES (1, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'Console', '', '', '[\"N\\u00e3o liga\",\"Superaquecimento\"]', 'sadsdsd', NULL, 'Aguardando Chegada', NULL, NULL, 'Pendente', '2025-09-27 17:25:36', '2025-09-27 17:25:36');
INSERT INTO `ordens_servico` VALUES (2, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'PlayStation 5', '', '', '[\"N\\u00e3o liga (Luz Azul)\"]', 'asdsds', NULL, 'Finalizado', 'PM123456789BR', 150.00, 'Aprovado', '2025-09-27 17:50:49', '2025-09-28 00:55:56');
INSERT INTO `ordens_servico` VALUES (3, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'PlayStation 5', '', '', '[\"N\\u00e3o liga (Luz Azul)\"]', 'asasasa', NULL, 'Cancelado', '', NULL, 'Pendente', '2025-09-27 18:41:23', '2025-09-27 20:33:40');
INSERT INTO `ordens_servico` VALUES (4, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'PC Gamer', 'Dell', 'G15', '[\"N\\u00e3o d\\u00e1 v\\u00eddeo\",\"Reiniciando sozinho\",\"Upgrade de Placa de V\\u00eddeo\"]', 'ajude', 'assets/uploads/os_media/os_68d89fc54fc2c.jpg', 'Aguardando Chegada', NULL, NULL, 'Pendente', '2025-09-27 23:39:01', '2025-09-27 23:39:01');
INSERT INTO `ordens_servico` VALUES (5, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'PC Gamer', 'aa', 'bb', '[\"Reiniciando sozinho\"]', 'AADADA', NULL, 'Aguardando Peças', '', NULL, 'Pendente', '2025-09-28 00:56:24', '2025-09-28 01:09:39');
INSERT INTO `ordens_servico` VALUES (6, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'PC Gamer', 'bbb', 'bbb', '[\"N\\u00e3o d\\u00e1 v\\u00eddeo\"]', 'bbbbb', NULL, 'Aguardando Chegada', NULL, NULL, 'Pendente', '2025-09-28 01:41:47', '2025-09-28 01:41:47');
INSERT INTO `ordens_servico` VALUES (7, 'Rodrigo Rios', '(71) 99911-3915', '', '44820-053', 'Rua Santo Antônio', '580', 'Oliveira', 'Capim Grosso', 'BA', 'PC Gamer', 'dd', 'ddd', '[\"Reiniciando sozinho\",\"Upgrade de Placa de V\\u00eddeo\"]', 'ddddd', NULL, 'Aguardando Chegada', NULL, NULL, 'Pendente', '2025-09-28 01:43:34', '2025-09-28 01:43:34');

-- ----------------------------
-- Table structure for os_historico
-- ----------------------------
DROP TABLE IF EXISTS `os_historico`;
CREATE TABLE `os_historico`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `os_id` int NOT NULL,
  `status_novo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `data_alteracao` timestamp NOT NULL DEFAULT current_timestamp,
  `observacao` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `os_id`(`os_id` ASC) USING BTREE,
  CONSTRAINT `os_historico_ibfk_1` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of os_historico
-- ----------------------------
INSERT INTO `os_historico` VALUES (1, 1, 'Aguardando Chegada', '2025-09-27 17:25:36', 'OS criada pelo cliente via site.');
INSERT INTO `os_historico` VALUES (2, 2, 'Aguardando Chegada', '2025-09-27 17:50:49', 'OS criada pelo cliente via site.');
INSERT INTO `os_historico` VALUES (3, 2, 'Em Análise', '2025-09-27 18:21:37', 'Status alterado pelo técnico.');
INSERT INTO `os_historico` VALUES (4, 2, 'Orçamento Enviado', '2025-09-27 18:26:25', 'Status alterado pelo técnico.');
INSERT INTO `os_historico` VALUES (5, 2, 'Aprovado | Em Reparo', '2025-09-27 18:27:03', 'Orçamento aprovado pelo cliente via site.');
INSERT INTO `os_historico` VALUES (6, 2, 'Enviado de Volta', '2025-09-27 18:29:37', 'Status alterado pelo técnico.');
INSERT INTO `os_historico` VALUES (7, 3, 'Aguardando Chegada', '2025-09-27 18:41:23', 'OS criada pelo cliente via site.');
INSERT INTO `os_historico` VALUES (8, 3, 'Cancelado', '2025-09-27 20:33:40', 'Status alterado pelo técnico.');
INSERT INTO `os_historico` VALUES (9, 4, 'Aguardando Chegada', '2025-09-27 23:39:01', 'OS criada pelo cliente via site.');
INSERT INTO `os_historico` VALUES (10, 2, 'Finalizado', '2025-09-28 00:55:56', 'Status alterado pelo técnico.');
INSERT INTO `os_historico` VALUES (11, 5, 'Aguardando Chegada', '2025-09-28 00:56:24', 'OS criada pelo cliente via site.');
INSERT INTO `os_historico` VALUES (12, 5, 'Aguardando Peças', '2025-09-28 01:09:39', 'Status alterado pelo técnico.');
INSERT INTO `os_historico` VALUES (13, 6, 'Aguardando Chegada', '2025-09-28 01:41:47', 'OS criada pelo cliente via site.');
INSERT INTO `os_historico` VALUES (14, 7, 'Aguardando Chegada', '2025-09-28 01:43:34', 'OS criada pelo cliente via site.');

-- ----------------------------
-- Table structure for os_statuses
-- ----------------------------
DROP TABLE IF EXISTS `os_statuses`;
CREATE TABLE `os_statuses`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `color_bg` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#E5E7EB',
  `color_text` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#374151',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of os_statuses
-- ----------------------------
INSERT INTO `os_statuses` VALUES (1, 'Aguardando Chegada', '#FEF3C7', '#92400E');
INSERT INTO `os_statuses` VALUES (2, 'Em Análise', '#DBEAFE', '#1E40AF');
INSERT INTO `os_statuses` VALUES (3, 'Orçamento Enviado', '#FCE7F3', '#9D174D');
INSERT INTO `os_statuses` VALUES (4, 'Aprovado | Em Reparo', '#D1FAE5', '#065F46');
INSERT INTO `os_statuses` VALUES (5, 'Aguardando Peças', '#FEE2E2', '#991B1B');
INSERT INTO `os_statuses` VALUES (6, 'Finalizado', '#E0E7FF', '#312E81');
INSERT INTO `os_statuses` VALUES (7, 'Enviado de Volta', '#F0F9FF', '#0C4A6E');
INSERT INTO `os_statuses` VALUES (8, 'Cancelado', '#F3F4F6', '#4B5563');

-- ----------------------------
-- Table structure for services
-- ----------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon_svg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of services
-- ----------------------------
INSERT INTO `services` VALUES (2, '<svg width=\"800px\" height=\"800px\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\r\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M2 6C2 4.34315 3.34315 3 5 3H19C20.6569 3 22 4.34315 22 6V15C22 16.6569 20.6569 18 19 18H13V19H15C15.5523 19 16 19.4477 16 20C16 20.5523 15.5523 21 15 21H9C8.44772 21 8 20.5523 8 20C8 19.4477 8.44772 19 9 19H11V18H5C3.34315 18 2 16.6569 2 15V6ZM5 5C4.44772 5 4 5.44772 4 6V15C4 15.5523 4.44772 16 5 16H19C19.5523 16 20 15.5523 20 15V6C20 5.44772 19.5523 5 19 5H5Z\" fill=\"#000000\"/>\r\n</svg>', 'Manutenção de PC Gamer', 'Limpeza completa, troca de pasta térmica, upgrades de hardware e otimização de performance para máxima jogatina.', 2, 1);
INSERT INTO `services` VALUES (3, '<svg fill=\"#000000\" width=\"800px\" height=\"800px\" viewBox=\"0 0 32 32\" xmlns=\"http://www.w3.org/2000/svg\">\r\n\r\n<title/>\r\n\r\n<g data-name=\"Layer 2\" id=\"Layer_2\">\r\n\r\n<path d=\"M27.28,28H4.72A2,2,0,0,1,3,25H3l2.85-5a2,2,0,0,1,1.74-1H24.42a2,2,0,0,1,1.74,1L29,25a2,2,0,0,1-1.73,3ZM4.72,26H27.28l-2.86-5H7.58Z\"/>\r\n\r\n<path d=\"M25,21H7a1,1,0,0,1-1-1V6A2,2,0,0,1,8,4H24a2,2,0,0,1,2,2V20A1,1,0,0,1,25,21ZM8,19H24V6H8Z\"/>\r\n\r\n<path d=\"M18,25H14a1,1,0,0,1,0-2h4a1,1,0,0,1,0,2Z\"/>\r\n\r\n</g>\r\n\r\n</svg>', 'Conserto de Notebooks', 'Reparamos telas, teclados, baterias e problemas de placa-mãe em todas as principais marcas.', 3, 1);
INSERT INTO `services` VALUES (4, '<svg width=\"800px\" height=\"800px\" viewBox=\"0 0 128 128\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">\r\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M73.2535 9.26712C73.2076 8.96978 72.8997 8.78091 72.5894 8.89669L35.3332 22.7959C35.1356 22.8696 35.0057 23.0592 35.0082 23.2698C35.0537 27.1119 35.11 30.9631 35.1684 34.9607C35.3358 46.4208 35.5208 59.0837 35.5207 76.1812C35.5207 82.7637 35.4728 88.2844 35.4304 93.1746C35.3616 101.113 35.3071 107.39 35.4951 113.851C35.5023 114.097 35.6768 114.287 35.9024 114.318C46.8446 115.804 71.3821 115.589 83.4883 115.265C83.7651 115.257 83.9762 115.032 83.9667 114.757C82.664 77.3677 76.416 29.7482 73.2535 9.26712ZM72.2398 7.95977C73.1153 7.63317 74.0946 8.16106 74.2418 9.11452C77.4065 29.6102 83.6613 77.2747 84.9661 114.723C84.995 115.554 84.3452 116.242 83.5151 116.264C71.4296 116.589 46.8059 116.808 35.7679 115.309C35.0395 115.21 34.5164 114.597 34.4956 113.88C34.3071 107.403 34.3617 101.095 34.4306 93.1384C34.4729 88.2493 34.5207 82.7376 34.5207 76.1812C34.5208 59.0916 34.3359 46.4389 34.1685 34.9801C34.1101 30.9812 34.0538 27.1276 34.0082 23.2817C34.0007 22.649 34.3912 22.08 34.9837 21.859L72.2398 7.95977Z\" fill=\"#000000\"/>\r\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M85.5789 60.6637C85.855 60.6637 86.0789 60.8876 86.0789 61.1637V65.3334C86.0789 65.6095 85.855 65.8334 85.5789 65.8334C85.3027 65.8334 85.0789 65.6095 85.0789 65.3334V61.1637C85.0789 60.8876 85.3027 60.6637 85.5789 60.6637Z\" fill=\"#000000\"/>\r\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M93.9593 11.4087C94.0113 11.8118 94.0058 12.2548 93.9851 12.6991C91.8525 58.3706 93.1111 99.5828 93.9992 114.971C94.0151 115.247 93.8048 115.483 93.5294 115.499C93.4594 115.503 93.3892 115.507 93.3187 115.512C91.2409 115.634 88.8956 115.773 84.9652 115.499C84.6897 115.48 84.482 115.241 84.5012 114.965C84.5204 114.69 84.7593 114.482 85.0348 114.501C88.7228 114.759 90.9813 114.647 92.9724 114.53C92.0847 98.7361 90.8744 57.879 92.9861 12.6524C93.0063 12.2208 93.0079 11.8495 92.9675 11.5367C92.9271 11.2235 92.8493 11.0087 92.7411 10.8626C92.5594 10.6173 92.1322 10.3731 90.8988 10.5732C90.4078 10.6529 88.5544 11.1133 85.7003 11.8542C82.8635 12.5905 79.0728 13.5944 74.738 14.7488C74.4712 14.8198 74.1973 14.6611 74.1262 14.3943C74.0552 14.1274 74.2139 13.8535 74.4807 13.7825C78.8156 12.628 82.6089 11.6234 85.449 10.8862C88.2719 10.1535 90.1883 9.67541 90.7387 9.58611C92.0655 9.37085 93.0126 9.54886 93.5447 10.2675C93.7935 10.6034 93.9074 11.0061 93.9593 11.4087ZM37.0018 115.458C37.0252 115.182 37.2673 114.978 37.5424 115.002C40.4112 115.246 43.433 115.444 46.5256 115.602C46.8014 115.616 47.0135 115.851 46.9993 116.127C46.9852 116.403 46.7502 116.615 46.4744 116.601C43.3729 116.442 40.3397 116.243 37.4576 115.998C37.1825 115.975 36.9784 115.733 37.0018 115.458Z\" fill=\"#000000\"/>\r\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M86.9268 16.0788C85.8467 14.7532 84.5898 14.4386 83.8183 14.5093C83.8111 14.5099 83.8039 14.5104 83.7967 14.5108L75.164 14.9255C75.2093 15.2025 75.2711 15.5874 75.3497 16.091C75.536 17.2849 75.8164 19.1458 76.1933 21.8174C76.947 27.1606 78.0871 35.7468 79.6329 48.7243C82.8124 75.4178 84.6146 102.357 84.9875 114.349C86.7906 114.623 89.3279 114.47 90.9891 114.318C90.863 109.002 90.6793 101.623 90.473 93.3331C89.8197 67.0836 88.9394 31.7111 88.9394 23.8721C88.9394 19.7889 88.0099 17.4081 86.9268 16.0788ZM74.1118 14.5347C74.0856 14.5393 74.0856 14.5393 74.0856 14.5393L74.0866 14.5448L74.0897 14.5629L74.1024 14.6373C74.1138 14.7042 74.1309 14.8061 74.1539 14.9453C74.1998 15.2236 74.2689 15.6509 74.3617 16.2452C74.5471 17.4337 74.8268 19.2898 75.2031 21.9571C75.9557 27.2918 77.0948 35.8708 78.6399 48.8426C81.8529 75.8171 83.6573 103.038 84.0002 114.785C84.007 115.018 84.1732 115.215 84.4012 115.261C86.5473 115.693 89.8439 115.44 91.5501 115.268C91.8101 115.242 92.006 115.02 91.9999 114.759C91.8733 109.405 91.6847 101.826 91.4721 93.2839C90.8189 67.0368 89.9394 31.6973 89.9394 23.8721C89.9394 19.6515 88.98 17.0155 87.702 15.4471C86.4253 13.8803 84.8551 13.4142 83.7384 13.5124L74.5541 13.9537C74.4108 13.9605 74.2773 14.0287 74.1877 14.1407C74.098 14.2528 74.0609 14.398 74.0856 14.5393L74.1118 14.5347Z\" fill=\"#000000\"/>\r\n<path fill-rule=\"evenodd\" clip-rule=\"evenodd\" d=\"M46.5183 114.795C46.5643 114.56 46.7699 114.391 47.009 114.391C54.5207 114.391 61.1803 114.762 67.1932 115.133C67.814 115.171 68.4277 115.209 69.0345 115.247C74.3122 115.576 79.0765 115.873 83.5 115.873C83.725 115.873 83.9222 116.023 83.982 116.24L84.982 119.867C85.0235 120.018 84.9923 120.179 84.8978 120.303C84.8032 120.427 84.6561 120.5 84.5 120.5H46.009C45.8596 120.5 45.718 120.433 45.623 120.318C45.528 120.202 45.4896 120.051 45.5183 119.904L46.5183 114.795ZM47.4206 115.391L46.6164 119.5H83.8435L83.119 116.872C78.7701 116.856 74.0964 116.564 68.9752 116.245C68.3671 116.207 67.7527 116.169 67.1317 116.131C61.2331 115.767 54.732 115.405 47.4206 115.391Z\" fill=\"#000000\"/>\r\n</svg>', 'Reparo de Consoles', 'Especialistas em PlayStation, Xbox e Nintendo. Do leitor de disco à fonte de alimentação, nós resolvemos.', 1, 1);

-- ----------------------------
-- Table structure for social_links
-- ----------------------------
DROP TABLE IF EXISTS `social_links`;
CREATE TABLE `social_links`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon_class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` int NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of social_links
-- ----------------------------
INSERT INTO `social_links` VALUES (1, 'fab fa-instagram', 'https://instagram.com', 1);
INSERT INTO `social_links` VALUES (2, 'fab fa-facebook', 'https://facebook.com', 2);
INSERT INTO `social_links` VALUES (3, 'fab fa-whatsapp', 'https://wa.me/5571999999999', 3);

-- ----------------------------
-- Table structure for usuarios
-- ----------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `login` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nivel_acesso` enum('Admin','Assistente') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Assistente',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `login`(`login` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios
-- ----------------------------
INSERT INTO `usuarios` VALUES (1, 'Administrador', 'admin@reparopro.com', '$2y$10$Si7lnPjIw1XlfjFF3uoC.ORum7shxrQ86McAkkfrF4L2VZgQsHSgy', 'Admin');

SET FOREIGN_KEY_CHECKS = 1;
