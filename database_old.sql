-- Criado em: 2025-09-27
-- SGBD: MySQL
-- Descrição: Estrutura completa do banco de dados para a plataforma Central de Reparos PRO.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Estrutura da tabela `avaliacoes`
--
CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `os_id` int(11) NOT NULL,
  `cliente_nome` varchar(255) NOT NULL,
  `nota_estrelas` tinyint(1) NOT NULL,
  `comentario` text,
  `status` enum('Pendente','Aprovado') NOT NULL DEFAULT 'Pendente',
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `os_id` (`os_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estrutura da tabela `config_formulario`
--
CREATE TABLE `config_formulario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('categoria','problema_comum') NOT NULL,
  `valor` varchar(255) NOT NULL,
  `categoria_pai_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Inserindo dados de exemplo para `config_formulario`
--
INSERT INTO `config_formulario` (`id`, `tipo`, `valor`, `categoria_pai_id`) VALUES
(1, 'categoria', 'Console', NULL),
(2, 'categoria', 'PC Gamer', NULL),
(3, 'categoria', 'Notebook', NULL),
(4, 'categoria', 'Celular', NULL),
(5, 'problema_comum', 'Não liga', 1),
(6, 'problema_comum', 'Superaquecimento', 1),
(7, 'problema_comum', 'Erro de leitura de disco', 1),
(8, 'problema_comum', 'Problema na entrada HDMI', 1),
(9, 'problema_comum', 'Não dá vídeo', 2),
(10, 'problema_comum', 'Tela quebrada', 3),
(11, 'problema_comum', 'Bateria não carrega', 3),
(12, 'problema_comum', 'Tela quebrada', 4);

--
-- Estrutura da tabela `config_site`
--
CREATE TABLE `config_site` (
  `config_key` varchar(100) NOT NULL,
  `config_value` text,
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Inserindo dados de exemplo para `config_site`
--
INSERT INTO `config_site` (`config_key`, `config_value`) VALUES
('area_abrangencia', '{\"cidades\":[\"São Paulo\",\"Guarulhos\",\"Osasco\"],\"estados\":[\"SP\"]}'),
('banner_principal_img', 'assets/img/default_banner.jpg'),
('cor_primaria', '#3b82f6'),
('facebook_pixel_id', ''),
('guia_envio_conteudo', '<p>Aqui vão as instruções detalhadas de como embalar e enviar seu equipamento com segurança. Edite este texto no painel administrativo!</p>'),
('logo_path', 'assets/img/logo.png'),
('telegram_chat_id', ''),
('telegram_token', '');

--
-- Estrutura da tabela `ordens_servico`
--
CREATE TABLE `ordens_servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_nome` varchar(255) NOT NULL,
  `cliente_whatsapp` varchar(20) NOT NULL,
  `cliente_email` varchar(255) DEFAULT NULL,
  `cep` varchar(10) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `dispositivo_categoria` varchar(100) NOT NULL,
  `dispositivo_marca` varchar(100) DEFAULT NULL,
  `dispositivo_modelo` varchar(100) DEFAULT NULL,
  `problemas_selecionados` text,
  `descricao_problema` text,
  `media_path` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Aguardando Chegada',
  `codigo_rastreio_devolucao` varchar(100) DEFAULT NULL,
  `valor_orcamento` decimal(10,2) DEFAULT NULL,
  `orcamento_status` enum('Pendente','Aprovado','Recusado') NOT NULL DEFAULT 'Pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estrutura da tabela `os_historico`
--
CREATE TABLE `os_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `os_id` int(11) NOT NULL,
  `status_novo` varchar(100) NOT NULL,
  `data_alteracao` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacao` text,
  PRIMARY KEY (`id`),
  KEY `os_id` (`os_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Estrutura da tabela `usuarios`
--
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nivel_acesso` enum('Admin','Assistente') NOT NULL DEFAULT 'Assistente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Inserindo dados de exemplo para `usuarios`
--
INSERT INTO `usuarios` (`id`, `nome`, `login`, `senha`, `nivel_acesso`) VALUES
(1, 'Administrador', 'admin@reparopro.com', '$2y$10$f/9f.R3gYd9bI.V1m/hKz.uDZuJ0/x9gJ42F1.Kx5Y2t.4E5.mGv2', 'Admin');

--
-- Adicionando chaves estrangeiras
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `os_historico`
  ADD CONSTRAINT `os_historico_ibfk_1` FOREIGN KEY (`os_id`) REFERENCES `ordens_servico` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;