-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 31 mai 2024 à 22:32
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `omnesimmobilier`
--

-- --------------------------------------------------------

--
-- Structure de la table `agents`
--

DROP TABLE IF EXISTS `agents`;
CREATE TABLE IF NOT EXISTS `agents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prénom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `numéro_téléphone` varchar(20) DEFAULT NULL,
  `cv` text,
  `photo` text,
  `utilisateur_id` int NOT NULL,
  `specialité` enum('Immobilier résidentiel','Immobilier commercial','Terrain','Appartement à louer') DEFAULT 'Immobilier résidentiel',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_utilisateur` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `agents`
--

INSERT INTO `agents` (`id`, `nom`, `prénom`, `email`, `numéro_téléphone`, `cv`, `photo`, `utilisateur_id`, `specialité`) VALUES
(1, 'Dupont-Dumas', 'Alexandre', 'alexandre.dupont.dumas@gmail.com', '0601010101', 'CV/AlexandreDupont_Dumas', 'p/P1', 9, 'Appartement à louer'),
(2, 'Dubois', 'Gregory', 'gregory.dubois@gmail.com', '0602020202', 'CV/GregoryDubois', 'p/Photo_GregoryDubois', 10, 'Immobilier commercial'),
(3, 'Dupont', 'Marie', 'marie.dupont@gmail.com', '0603030303', 'CV/MarieDupont', 'p/Photo_MarieDupont', 11, 'Appartement à louer'),
(4, 'Landot', 'Pierre', 'pierre.landot@gmail.com', '0604040404', 'CV/PierreLandot', 'p/Photo_PierreLanclot', 12, 'Terrain'),
(5, 'Brun', 'Sabrina', 'sabrina.brun@gmail.com', '0605050505', 'CV/SabrinaBrun', 'p/Photo_SabrinaBrun', 13, 'Immobilier résidentiel');

-- --------------------------------------------------------

--
-- Structure de la table `cartes_credit`
--

DROP TABLE IF EXISTS `cartes_credit`;
CREATE TABLE IF NOT EXISTS `cartes_credit` (
  `id` int NOT NULL AUTO_INCREMENT,
  `paiement_id` int NOT NULL,
  `nom_sur_carte` varchar(100) NOT NULL,
  `numero_carte` varchar(20) NOT NULL,
  `date_expiration` varchar(5) NOT NULL,
  `code_securite` varchar(4) NOT NULL,
  `adresse_facturation` text NOT NULL,
  `type_carte` varchar(20) NOT NULL,
  `limite_credit` decimal(10,2) NOT NULL DEFAULT '5000.00',
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `paiement_id` (`paiement_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `cartes_credit`
--

INSERT INTO `cartes_credit` (`id`, `paiement_id`, `nom_sur_carte`, `numero_carte`, `date_expiration`, `code_securite`, `adresse_facturation`, `type_carte`, `limite_credit`, `balance`) VALUES
(10, 23, 'Nahon', '782232378', '20/02', '123', 'egzerherherh', 'Visa', 1202.00, 1200.00);

-- --------------------------------------------------------

--
-- Structure de la table `disponibilités_agents`
--

DROP TABLE IF EXISTS `disponibilités_agents`;
CREATE TABLE IF NOT EXISTS `disponibilités_agents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` int DEFAULT NULL,
  `jour` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
  `créneau_horaire` varchar(20) NOT NULL,
  `disponible` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=321 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `disponibilités_agents`
--

INSERT INTO `disponibilités_agents` (`id`, `agent_id`, `jour`, `créneau_horaire`, `disponible`) VALUES
(1, 1, 'Lundi', '09:00-10:00', 0),
(2, 1, 'Lundi', '10:00-11:00', 1),
(3, 1, 'Lundi', '11:00-12:00', 0),
(4, 1, 'Lundi', '13:00-14:00', 1),
(5, 1, 'Lundi', '14:00-15:00', 1),
(6, 1, 'Lundi', '15:00-16:00', 1),
(7, 1, 'Lundi', '16:00-17:00', 1),
(8, 1, 'Lundi', '17:00-18:00', 1),
(9, 1, 'Mardi', '09:00-10:00', 1),
(10, 1, 'Mardi', '10:00-11:00', 1),
(11, 1, 'Mardi', '11:00-12:00', 0),
(12, 1, 'Mardi', '13:00-14:00', 1),
(13, 1, 'Mardi', '14:00-15:00', 1),
(14, 1, 'Mardi', '15:00-16:00', 1),
(15, 1, 'Mardi', '16:00-17:00', 1),
(16, 1, 'Mardi', '17:00-18:00', 1),
(17, 1, 'Mercredi', '09:00-10:00', 1),
(18, 1, 'Mercredi', '10:00-11:00', 1),
(19, 1, 'Mercredi', '11:00-12:00', 1),
(20, 1, 'Mercredi', '13:00-14:00', 1),
(21, 1, 'Mercredi', '14:00-15:00', 1),
(22, 1, 'Mercredi', '15:00-16:00', 1),
(23, 1, 'Mercredi', '16:00-17:00', 1),
(24, 1, 'Mercredi', '17:00-18:00', 1),
(25, 1, 'Jeudi', '09:00-10:00', 1),
(26, 1, 'Jeudi', '10:00-11:00', 1),
(27, 1, 'Jeudi', '11:00-12:00', 1),
(28, 1, 'Jeudi', '13:00-14:00', 1),
(29, 1, 'Jeudi', '14:00-15:00', 1),
(30, 1, 'Jeudi', '15:00-16:00', 1),
(31, 1, 'Jeudi', '16:00-17:00', 1),
(32, 1, 'Jeudi', '17:00-18:00', 1),
(33, 1, 'Vendredi', '09:00-10:00', 1),
(34, 1, 'Vendredi', '10:00-11:00', 1),
(35, 1, 'Vendredi', '11:00-12:00', 1),
(36, 1, 'Vendredi', '13:00-14:00', 1),
(37, 1, 'Vendredi', '14:00-15:00', 1),
(38, 1, 'Vendredi', '15:00-16:00', 1),
(39, 1, 'Vendredi', '16:00-17:00', 1),
(40, 1, 'Vendredi', '17:00-18:00', 1),
(41, 2, 'Lundi', '09:00-10:00', 1),
(42, 2, 'Lundi', '10:00-11:00', 1),
(43, 2, 'Lundi', '11:00-12:00', 1),
(44, 2, 'Lundi', '13:00-14:00', 1),
(45, 2, 'Lundi', '14:00-15:00', 1),
(46, 2, 'Lundi', '15:00-16:00', 1),
(47, 2, 'Lundi', '16:00-17:00', 1),
(48, 2, 'Lundi', '17:00-18:00', 1),
(49, 2, 'Mardi', '09:00-10:00', 1),
(50, 2, 'Mardi', '10:00-11:00', 1),
(51, 2, 'Mardi', '11:00-12:00', 1),
(52, 2, 'Mardi', '13:00-14:00', 1),
(53, 2, 'Mardi', '14:00-15:00', 1),
(54, 2, 'Mardi', '15:00-16:00', 1),
(55, 2, 'Mardi', '16:00-17:00', 1),
(56, 2, 'Mardi', '17:00-18:00', 1),
(57, 2, 'Mercredi', '09:00-10:00', 1),
(58, 2, 'Mercredi', '10:00-11:00', 1),
(59, 2, 'Mercredi', '11:00-12:00', 1),
(60, 2, 'Mercredi', '13:00-14:00', 1),
(61, 2, 'Mercredi', '14:00-15:00', 1),
(62, 2, 'Mercredi', '15:00-16:00', 1),
(63, 2, 'Mercredi', '16:00-17:00', 1),
(64, 2, 'Mercredi', '17:00-18:00', 1),
(65, 2, 'Jeudi', '09:00-10:00', 1),
(66, 2, 'Jeudi', '10:00-11:00', 1),
(67, 2, 'Jeudi', '11:00-12:00', 1),
(68, 2, 'Jeudi', '13:00-14:00', 1),
(69, 2, 'Jeudi', '14:00-15:00', 1),
(70, 2, 'Jeudi', '15:00-16:00', 1),
(71, 2, 'Jeudi', '16:00-17:00', 1),
(72, 2, 'Jeudi', '17:00-18:00', 1),
(73, 2, 'Vendredi', '09:00-10:00', 1),
(74, 2, 'Vendredi', '10:00-11:00', 1),
(75, 2, 'Vendredi', '11:00-12:00', 1),
(76, 2, 'Vendredi', '13:00-14:00', 1),
(77, 2, 'Vendredi', '14:00-15:00', 1),
(78, 2, 'Vendredi', '15:00-16:00', 1),
(79, 2, 'Vendredi', '16:00-17:00', 1),
(80, 2, 'Vendredi', '17:00-18:00', 1),
(81, 3, 'Lundi', '09:00-10:00', 1),
(82, 3, 'Lundi', '10:00-11:00', 1),
(83, 3, 'Lundi', '11:00-12:00', 1),
(84, 3, 'Lundi', '13:00-14:00', 1),
(85, 3, 'Lundi', '14:00-15:00', 1),
(86, 3, 'Lundi', '15:00-16:00', 1),
(87, 3, 'Lundi', '16:00-17:00', 1),
(88, 3, 'Lundi', '17:00-18:00', 1),
(89, 3, 'Mardi', '09:00-10:00', 0),
(90, 3, 'Mardi', '10:00-11:00', 1),
(91, 3, 'Mardi', '11:00-12:00', 1),
(92, 3, 'Mardi', '13:00-14:00', 1),
(93, 3, 'Mardi', '14:00-15:00', 1),
(94, 3, 'Mardi', '15:00-16:00', 1),
(95, 3, 'Mardi', '16:00-17:00', 1),
(96, 3, 'Mardi', '17:00-18:00', 1),
(97, 3, 'Mercredi', '09:00-10:00', 1),
(98, 3, 'Mercredi', '10:00-11:00', 0),
(99, 3, 'Mercredi', '11:00-12:00', 1),
(100, 3, 'Mercredi', '13:00-14:00', 1),
(101, 3, 'Mercredi', '14:00-15:00', 1),
(102, 3, 'Mercredi', '15:00-16:00', 1),
(103, 3, 'Mercredi', '16:00-17:00', 1),
(104, 3, 'Mercredi', '17:00-18:00', 1),
(105, 3, 'Jeudi', '09:00-10:00', 1),
(106, 3, 'Jeudi', '10:00-11:00', 1),
(107, 3, 'Jeudi', '11:00-12:00', 1),
(108, 3, 'Jeudi', '13:00-14:00', 1),
(109, 3, 'Jeudi', '14:00-15:00', 1),
(110, 3, 'Jeudi', '15:00-16:00', 1),
(111, 3, 'Jeudi', '16:00-17:00', 1),
(112, 3, 'Jeudi', '17:00-18:00', 1),
(113, 3, 'Vendredi', '09:00-10:00', 1),
(114, 3, 'Vendredi', '10:00-11:00', 1),
(115, 3, 'Vendredi', '11:00-12:00', 1),
(116, 3, 'Vendredi', '13:00-14:00', 1),
(117, 3, 'Vendredi', '14:00-15:00', 1),
(118, 3, 'Vendredi', '15:00-16:00', 1),
(119, 3, 'Vendredi', '16:00-17:00', 1),
(120, 3, 'Vendredi', '17:00-18:00', 1),
(121, 4, 'Lundi', '09:00-10:00', 1),
(122, 4, 'Lundi', '10:00-11:00', 1),
(123, 4, 'Lundi', '11:00-12:00', 1),
(124, 4, 'Lundi', '13:00-14:00', 1),
(125, 4, 'Lundi', '14:00-15:00', 1),
(126, 4, 'Lundi', '15:00-16:00', 1),
(127, 4, 'Lundi', '16:00-17:00', 1),
(128, 4, 'Lundi', '17:00-18:00', 1),
(129, 4, 'Mardi', '09:00-10:00', 1),
(130, 4, 'Mardi', '10:00-11:00', 1),
(131, 4, 'Mardi', '11:00-12:00', 1),
(132, 4, 'Mardi', '13:00-14:00', 1),
(133, 4, 'Mardi', '14:00-15:00', 1),
(134, 4, 'Mardi', '15:00-16:00', 1),
(135, 4, 'Mardi', '16:00-17:00', 1),
(136, 4, 'Mardi', '17:00-18:00', 1),
(137, 4, 'Mercredi', '09:00-10:00', 1),
(138, 4, 'Mercredi', '10:00-11:00', 1),
(139, 4, 'Mercredi', '11:00-12:00', 1),
(140, 4, 'Mercredi', '13:00-14:00', 1),
(141, 4, 'Mercredi', '14:00-15:00', 1),
(142, 4, 'Mercredi', '15:00-16:00', 1),
(143, 4, 'Mercredi', '16:00-17:00', 1),
(144, 4, 'Mercredi', '17:00-18:00', 1),
(145, 4, 'Jeudi', '09:00-10:00', 1),
(146, 4, 'Jeudi', '10:00-11:00', 1),
(147, 4, 'Jeudi', '11:00-12:00', 1),
(148, 4, 'Jeudi', '13:00-14:00', 1),
(149, 4, 'Jeudi', '14:00-15:00', 1),
(150, 4, 'Jeudi', '15:00-16:00', 1),
(151, 4, 'Jeudi', '16:00-17:00', 1),
(152, 4, 'Jeudi', '17:00-18:00', 1),
(153, 4, 'Vendredi', '09:00-10:00', 1),
(154, 4, 'Vendredi', '10:00-11:00', 1),
(155, 4, 'Vendredi', '11:00-12:00', 1),
(156, 4, 'Vendredi', '13:00-14:00', 1),
(157, 4, 'Vendredi', '14:00-15:00', 1),
(158, 4, 'Vendredi', '15:00-16:00', 1),
(159, 4, 'Vendredi', '16:00-17:00', 1),
(160, 4, 'Vendredi', '17:00-18:00', 1),
(161, 5, 'Lundi', '09:00-10:00', 1),
(162, 5, 'Lundi', '10:00-11:00', 1),
(163, 5, 'Lundi', '11:00-12:00', 1),
(164, 5, 'Lundi', '13:00-14:00', 1),
(165, 5, 'Lundi', '14:00-15:00', 1),
(166, 5, 'Lundi', '15:00-16:00', 1),
(167, 5, 'Lundi', '16:00-17:00', 1),
(168, 5, 'Lundi', '17:00-18:00', 1),
(169, 5, 'Mardi', '09:00-10:00', 1),
(170, 5, 'Mardi', '10:00-11:00', 1),
(171, 5, 'Mardi', '11:00-12:00', 1),
(172, 5, 'Mardi', '13:00-14:00', 1),
(173, 5, 'Mardi', '14:00-15:00', 1),
(174, 5, 'Mardi', '15:00-16:00', 1),
(175, 5, 'Mardi', '16:00-17:00', 1),
(176, 5, 'Mardi', '17:00-18:00', 1),
(177, 5, 'Mercredi', '09:00-10:00', 1),
(178, 5, 'Mercredi', '10:00-11:00', 1),
(179, 5, 'Mercredi', '11:00-12:00', 1),
(180, 5, 'Mercredi', '13:00-14:00', 1),
(181, 5, 'Mercredi', '14:00-15:00', 1),
(182, 5, 'Mercredi', '15:00-16:00', 1),
(183, 5, 'Mercredi', '16:00-17:00', 1),
(184, 5, 'Mercredi', '17:00-18:00', 1),
(185, 5, 'Jeudi', '09:00-10:00', 1),
(186, 5, 'Jeudi', '10:00-11:00', 1),
(187, 5, 'Jeudi', '11:00-12:00', 1),
(188, 5, 'Jeudi', '13:00-14:00', 1),
(189, 5, 'Jeudi', '14:00-15:00', 1),
(190, 5, 'Jeudi', '15:00-16:00', 1),
(191, 5, 'Jeudi', '16:00-17:00', 1),
(192, 5, 'Jeudi', '17:00-18:00', 1),
(193, 5, 'Vendredi', '09:00-10:00', 1),
(194, 5, 'Vendredi', '10:00-11:00', 1),
(195, 5, 'Vendredi', '11:00-12:00', 1),
(196, 5, 'Vendredi', '13:00-14:00', 1),
(197, 5, 'Vendredi', '14:00-15:00', 1),
(198, 5, 'Vendredi', '15:00-16:00', 1),
(199, 5, 'Vendredi', '16:00-17:00', 1),
(200, 5, 'Vendredi', '17:00-18:00', 1);

-- --------------------------------------------------------

--
-- Structure de la table `historiques`
--

DROP TABLE IF EXISTS `historiques`;
CREATE TABLE IF NOT EXISTS `historiques` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` int DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  `propriété_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `heure` time NOT NULL,
  `détails` text,
  `adresse_rendez_vous` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `propriété_id` (`propriété_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `historiques`
--

INSERT INTO `historiques` (`id`, `agent_id`, `utilisateur_id`, `propriété_id`, `action`, `date`, `heure`, `détails`, `adresse_rendez_vous`) VALUES
(28, 1, 15, 1, 'Rendez-vous complété', '2024-06-03', '10:00:00', 'digit detenue par l\'agent', '12 Rue de Passy'),
(29, 3, 15, 2, 'Rendez-vous complété', '2024-05-31', '13:00:00', 'digit detenue par l\'agent', '20 Rue du Commerce '),
(30, 1, 15, 1, 'Rendez-vous complété', '2024-06-03', '10:00:00', 'digit detenue par l\'agent', '12 Rue de Passy'),
(31, 3, 15, 2, 'Rendez-vous complété', '2024-06-03', '09:00:00', 'digit detenue par l\'agent', '20 Rue du Commerce ');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `expéditeur_id` int DEFAULT NULL,
  `destinataire_id` int DEFAULT NULL,
  `message` text NOT NULL,
  `date` date NOT NULL,
  `heure` time NOT NULL,
  `type` enum('Texto','Audio','Vidéo','Courriel') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `expéditeur_id` (`expéditeur_id`),
  KEY `destinataire_id` (`destinataire_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `expéditeur_id`, `destinataire_id`, `message`, `date`, `heure`, `type`) VALUES
(1, 15, 1, 'bonjour', '2024-05-31', '23:43:02', 'Texto');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `propriété_id` int DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date` date NOT NULL,
  `moyen_de_paiement` enum('Carte de crédit','PayPal','Autre') NOT NULL,
  `confirmation` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `propriété_id` (`propriété_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `client_id`, `propriété_id`, `montant`, `date`, `moyen_de_paiement`, `confirmation`) VALUES
(21, 15, 2, 1600.00, '2024-05-31', 'Carte de crédit', 1),
(20, 15, 2, 1600.00, '2024-05-31', 'Carte de crédit', 1),
(19, 15, 2, 1600.00, '2024-05-31', 'Carte de crédit', 1),
(18, 15, 2, 1600.00, '2024-05-31', 'Carte de crédit', 1),
(17, 15, 1, 1200.00, '2024-05-31', 'Carte de crédit', 1),
(16, 15, 2, 1600.00, '2024-05-31', 'Carte de crédit', 1),
(15, 15, NULL, 0.00, '2024-05-31', 'Carte de crédit', 1),
(22, 15, NULL, 0.00, '2024-05-31', 'Carte de crédit', 1),
(23, 15, NULL, 0.00, '2024-05-31', 'Carte de crédit', 1),
(24, 15, 1, 1200.00, '2024-05-31', 'Carte de crédit', 1);

-- --------------------------------------------------------

--
-- Structure de la table `propriétés`
--

DROP TABLE IF EXISTS `propriétés`;
CREATE TABLE IF NOT EXISTS `propriétés` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('Résidentiel','Commercial','Terrain','Appartement') NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `ville` varchar(100) NOT NULL,
  `code_postal` varchar(10) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `superficie` decimal(10,2) DEFAULT NULL,
  `nombre_de_chambres` int DEFAULT NULL,
  `nombre_de_salles_de_bain` int DEFAULT NULL,
  `balcon` tinyint(1) DEFAULT NULL,
  `parking` tinyint(1) DEFAULT NULL,
  `photos` text,
  `videos` text,
  `disponible` tinyint(1) DEFAULT '1',
  `infosup` text,
  `Nombre_de_pièce` int DEFAULT NULL,
  `agent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `propriétés`
--

INSERT INTO `propriétés` (`id`, `type`, `adresse`, `ville`, `code_postal`, `description`, `prix`, `superficie`, `nombre_de_chambres`, `nombre_de_salles_de_bain`, `balcon`, `parking`, `photos`, `videos`, `disponible`, `infosup`, `Nombre_de_pièce`, `agent_id`) VALUES
(1, 'Appartement', '12 Rue de Passy', 'Paris', '75016', '\r\nCharmant studio à Passy, parfaitement équipé et situé dans un quartier élégant du 16ème arrondissement, idéal pour étudiants cherchant confort et accessibilité à Paris.', 1200.00, 35.00, 1, 1, 0, 0, 'Location/img1.1 \r\nLocation/img1.2 \r\nLocation/img1.3 \r\nLocation/img1.4', NULL, 1, 'digit detenue par l\'agent', 1, 1),
(2, 'Appartement', '20 Rue du Commerce ', 'Paris', '75015', 'Les prix indiqués sont : (1) basés sur une date d’entrée dans les lieux le premier jour où l’appartement est disponible à la location ; (2) applicables seulement si le montant de la location est payé intégralement à l’entrée dans les lieux, tout en étant susceptible d’être plus cher en cas de paiement mensuel ; (3) n’incluent pas les charges et taxes. Pour le montant exact de votre location, nous vous laissons le soin de vous rendre sur notre site ou de nous contacter directement.\r\n\r\nVivez en plein centre de Paris avec ce studio situé Champs Elysees .\r\nDémarrez votre nouvelle vie avec cet appartement meublé Blueground, qui propose un mobilier design et tendance . Bénéficiez d’un service client 24/24 et 7/7!\r\nNB : Nos appartements sont proposés en résidences secondaires ou baux sociétés et pour un an maximum.\r\n\r\nUn appartement conçu spécialement pour vous\r\n\r\nConçu avec soin avec des finitions sur mesure, des meubles tendances et une cuisine entièrement équipée et aménagée, vous vous sentirez vraiment chez vous dans cet appartement Blueground. Profitez d’un moment de détente dans votre salon avec nos TV connectée ou nos enceintes Marshall wifi, ou d’un peu de repos bien mérité sur nos matelas haut de gamme ! Vous tomberez amoureux de cet appartement situé Champs Elysees.\r\n\r\nÉquipements\r\n\r\nLes équipements de l’immeuble propres à ce studio sont:\r\n\r\nAscenseur\r\nMachine à laver\r\nAssistance à l’arrivée et au quotidien\r\n\r\nTout l’appartement est à vous ! Vous serez accueilli par un membre de l’équipe de Blueground ou nous vous donnerons des instructions pour entrer dans les lieux par vous-même.\r\n\r\nTout au long de votre séjour, vous pourrez contacter notre service client via l’application Blueground. Vous pouvez commander des prestations de ménage supplémentaires, soumettre des demandes de maintenance et en savoir plus sur votre nouveau chez-vous en quelques clics.\r\n\r\nNous vous communiquerons toutes les informations nécessaires lors de la confirmation de votre séjour.', 1600.00, 29.00, 1, 1, 0, 0, 'Location/img2.1 \r\nLocation/img2.2 \r\nLocation/img2.3 \r\nLocation/img2.4 \r\nLocation/img2.5', NULL, 1, 'digit detenue par l\'agent', 1, 3);

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

DROP TABLE IF EXISTS `rendez_vous`;
CREATE TABLE IF NOT EXISTS `rendez_vous` (
  `id` int NOT NULL AUTO_INCREMENT,
  `propriété_id` int DEFAULT NULL,
  `agent_id` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `adresse` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `heure` time NOT NULL,
  `confirmation` tinyint(1) DEFAULT '0',
  `commentaires` text,
  `completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `propriété_id` (`propriété_id`),
  KEY `agent_id` (`agent_id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `rendez_vous`
--

INSERT INTO `rendez_vous` (`id`, `propriété_id`, `agent_id`, `client_id`, `adresse`, `date`, `heure`, `confirmation`, `commentaires`, `completed`) VALUES
(53, 1, 1, 15, '12 Rue de Passy', '2024-06-03', '11:00:00', NULL, 'digit detenue par l\'agent', 0),
(54, 1, 1, 15, '12 Rue de Passy', '2024-06-04', '11:00:00', NULL, 'digit detenue par l\'agent', 0);

--
-- Déclencheurs `rendez_vous`
--
DROP TRIGGER IF EXISTS `after_rendez_vous_update`;
DELIMITER $$
CREATE TRIGGER `after_rendez_vous_update` AFTER UPDATE ON `rendez_vous` FOR EACH ROW BEGIN
    IF NEW.completed = 1 THEN
        INSERT INTO historiques (agent_id, utilisateur_id, propriété_id, action, date, heure, détails, adresse_rendez_vous)
        VALUES (NEW.agent_id, NEW.client_id, NEW.propriété_id, 'Rendez-vous complété', NEW.date, NEW.heure, NEW.commentaires, NEW.adresse);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prénom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `type_utilisateur` enum('Admin','Agent','Client') NOT NULL,
  `adresse1` varchar(255) DEFAULT NULL,
  `adresse2` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `numéro_téléphone` varchar(20) DEFAULT NULL,
  `cv` text,
  `photo` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prénom`, `email`, `mot_de_passe`, `type_utilisateur`, `adresse1`, `adresse2`, `ville`, `code_postal`, `pays`, `numéro_téléphone`, `cv`, `photo`) VALUES
(15, 'Nahon', 'Yonathan', 'yonathan.nahon@icloud.com', '$2y$10$ba13tzPepsd2BQ8twelk0OL/0hxRp.H028f1qT.NKhGpwPvq.5B5W', 'Client', '97 rue de colombes', '', 'Courbevoie', '92400', 'France', '0610582342', NULL, NULL),
(9, 'Dupont-Dumas', 'Alexandre', 'alexandre.dupont.dumas@gmail.com', '$2y$10$1rnatQ1IrsaNqPxnddhDTu.dVs.W83iyKYLPrbZ3skSTfwdetGZdG', 'Agent', NULL, NULL, NULL, NULL, NULL, '0601010101', 'CV/AlexandreDupont_Dumas', 'p/P1'),
(10, 'Dubois', 'Gregory', 'gregory.dubois@gmail.com', '$2y$10$cG24zNhvJN8Zb4romX1TmOI.M8fcBtLVViXhAFSKTmPu0HUtYcGR6', 'Agent', NULL, NULL, NULL, NULL, NULL, '0602020202', 'CV/GregoryDubois', 'p/Photo_GregoryDubois'),
(11, 'Dupont', 'Marie', 'marie.dupont@gmail.com', '$2y$10$4FlhUw4wcSdGVjJTWSOSm.6USjSjnxirnUb0J5hiqL4KvKzZwjFY.', 'Agent', NULL, NULL, NULL, NULL, NULL, '0603030303', 'CV/MarieDupont', 'p/Photo_MarieDupont'),
(12, 'Landot', 'Pierre', 'pierre.landot@gmail.com', '$2y$10$QV7UhPkwkyF8JPdFKxBEOeZgL/80oE4.9KCgoQoup9DIZyl5nBKHq', 'Agent', NULL, NULL, NULL, NULL, NULL, '0604040404', 'CV/PierreLandot', 'p/Photo_PierreLandot'),
(13, 'Brun', 'Sabrina', 'sabrina.brun@gmail.com', '$2y$10$qQUeRyn2VAs/QYEFQHhZ0O6oUExL1FCzRReXtS1WWIZg3cfC1TywS', 'Agent', NULL, NULL, NULL, NULL, NULL, '0605050505', 'CV/SabrinaBrun', 'p/Photo_SabrinaBrun'),
(17, 'Bianchi', 'Gerad', 'Grardj@gmail.com', '$2y$10$khn3R/t6GHrXxXeXSrT.ae2c8oim3hgQHAPIDpmkUQ3thgRLiaIb.', 'Client', '97 rue de colombes', '', 'Courbevoie', '92400', 'France', '0610582333', NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
