-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 08/06/2025 às 23:07
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `nickname_UNIQUE` (`nickname`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `nickname`, `fullName`, `email`, `password_hash`, `dob`, `photo_path`) VALUES
(1, 'paulinfps', 'Paulo Daniel', 'paulodaniel0922@gmail.com', '$2y$10$JGPq3M2tLr.P/0IxoGr51O6GtN1vXZx2Ai0ql9Wv9dCcWh/Aad8rC', '21/08/2007', 'uploads/1_vito.png'),
(2, 'Andreza Quintas', NULL, 'andrezaquintasfic@gmail.com', '$2y$10$CDKsZDa/1ieJ8yJIqbL9Ten3KvTwNI4nltSf.YOrIfM88I548muTm', NULL, NULL),
(3, 'Gnzin', 'Gustavo Daniel', 'dinizgustavo38@gmail.com', '$2y$10$yAazE9OY8VN93XYqOr.7h.RtonnZ28ohoFy88aTskZOrhYDSBMO9W', '01/09/2007', 'uploads/3_imagem_2025-06-08_194422610.png'),
(4, 'lili', NULL, 'lilisesi@gmail.com', '$2y$10$.4c77dcieHIey9yizfAGTOwgvs5UzmoXC3hoLHCzygeyShL56aszi', NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `utilizadores`
--

DROP TABLE IF EXISTS `utilizadores`;
CREATE TABLE IF NOT EXISTS `utilizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nickname` varchar(50) NOT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `dob` varchar(20) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  UNIQUE KEY `nickname_UNIQUE` (`nickname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
