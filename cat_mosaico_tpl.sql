-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Set 19, 2022 alle 16:46
-- Versione del server: 10.4.18-MariaDB
-- Versione PHP: 7.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `malvanomarchesini`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `cat_mosaico_tpl`
--

DROP TABLE IF EXISTS `cat_mosaico_tpl`;
CREATE TABLE `cat_mosaico_tpl` (
  `tpl_hash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(50) UNSIGNED NOT NULL DEFAULT 0,
  `tpl_basename` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'versafix-1',
  `tpl_name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tpl_metadata` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tpl_content` longtext CHARACTER SET utf8mb4 NOT NULL,
  `tpl_html` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tpl_lastchange` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `cat_mosaico_tpl`
--
ALTER TABLE `cat_mosaico_tpl`
  ADD PRIMARY KEY (`tpl_hash`) USING BTREE,
  ADD KEY `user_id` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
