-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 25/08/2025 às 03:49
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `moveminds`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

DROP TABLE IF EXISTS `configuracoes`;
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `chave` varchar(50) NOT NULL,
  `valor` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`) VALUES
(1, 'tema_app', 'claro'),
(2, 'notificacoes_push', 'ativo'),
(3, 'unidade_medida', 'metrico');

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `dob` varchar(20) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `plano_assinatura` varchar(50) DEFAULT 'Move Start+',
  `google_fit_connected` tinyint(1) NOT NULL DEFAULT '0',
  `apple_health_connected` tinyint(1) NOT NULL DEFAULT '0',
  `push_notifications_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `email_promotions_enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `nickname_UNIQUE` (`nickname`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nickname`, `fullName`, `email`, `password_hash`, `dob`, `photo_path`, `plano_assinatura`, `google_fit_connected`, `apple_health_connected`, `push_notifications_enabled`, `email_promotions_enabled`) VALUES
(1, 'paulinfps', 'Paulo Daniel', 'paulodaniel0922@gmail.com', '$2y$10$JGPq3M2tLr.P/0IxoGr51O6GtN1vXZx2Ai0ql9Wv9dCcWh/Aad8rC', '21/08/2007', 'uploads/1_vito.png', 'Move Start+', 0, 0, 0, 0),
(2, 'Andreza Quintas', 'ANreza', 'andrezaquintasfic@gmail.com', '$2y$10$CDKsZDa/1ieJ8yJIqbL9Ten3KvTwNI4nltSf.YOrIfM88I548muTm', NULL, NULL, 'Move Start+', 0, 0, 1, 0),
(3, 'Gnzin', 'Gustavo Daniel', 'dinizgustavo38@gmail.com', '$2y$10$yAazE9OY8VN93XYqOr.7h.RtonnZ28ohoFy88aTskZOrhYDSBMO9W', '01/09/2007', 'uploads/3_imagem_2025-06-08_194422610.png', 'Move Start+', 0, 0, 1, 0),
(4, 'lili', 'lili', 'lilisesi@gmail.com', '$2y$10$.4c77dcieHIey9yizfAGTOwgvs5UzmoXC3hoLHCzygeyShL56aszi', NULL, NULL, 'Move Start+', 0, 0, 1, 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
