-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : jeu. 09 oct. 2025 à 11:36
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `restoweb`
--
CREATE DATABASE IF NOT EXISTS `restoweb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `restoweb`;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `idCommande` int(11) NOT NULL,
  `dateHeureCom` datetime DEFAULT NULL,
  `totalTTC` decimal(15,2) DEFAULT NULL,
  `typeCom` tinyint(1) DEFAULT NULL,
  `idEtat` int(11) NOT NULL,
  `idUtilisateur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`idCommande`, `dateHeureCom`, `totalTTC`, `typeCom`, `idEtat`, `idUtilisateur`) VALUES
(1, NULL, 74.80, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `etat`
--

CREATE TABLE `etat` (
  `idEtat` int(11) NOT NULL,
  `libEtat` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `etat`
--

INSERT INTO `etat` (`idEtat`, `libEtat`) VALUES
(1, 'initialisée'),
(2, 'finalisée'),
(3, 'calculée'),
(4, 'en attente'),
(5, 'abandonnée'),
(6, 'en préparation'),
(7, 'prête'),
(8, 'servie');

-- --------------------------------------------------------

--
-- Structure de la table `lignedecommande`
--

CREATE TABLE `lignedecommande` (
  `idCommande` int(11) NOT NULL,
  `idProduit` int(11) NOT NULL,
  `quantite` int(11) DEFAULT NULL,
  `totalHT` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `lignedecommande`
--

INSERT INTO `lignedecommande` (`idCommande`, `idProduit`, `quantite`, `totalHT`) VALUES
(1, 1, 8, 68.00);

--
-- Déclencheurs `lignedecommande`
--
DELIMITER $$
CREATE TRIGGER `after_ligne_insert` AFTER INSERT ON `lignedecommande` FOR EACH ROW BEGIN
DECLARE v_totalHT decimal(15,2) ;
DECLARE v_typeCom bool ;

SET v_totalHT = 0.0 ;
SET v_typeCom = 0 ;

SELECT SUM(totalHT) INTO v_totalHT 
FROM lignedecommande 
WHERE idCommande = new.idCommande;

SELECT typeCom INTO v_typeCom
FROM commande
WHERE idCommande = new.idCommande;

IF v_typeCom = 1 THEN
    UPDATE commande
    SET totalTTC = v_totalHT * 1.055;
ELSE
    UPDATE commande
    SET totalTTC = v_totalHT * 1.1;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_ligne_update` AFTER UPDATE ON `lignedecommande` FOR EACH ROW BEGIN
DECLARE v_totalHT decimal(15,2) ;
DECLARE v_typeCom bool ;

SET v_totalHT = 0.0 ;
SET v_typeCom = 0 ;

SELECT SUM(totalHT) INTO v_totalHT 
FROM lignedecommande 
WHERE idCommande = new.idCommande;

SELECT typeCom INTO v_typeCom
FROM commande
WHERE idCommande = new.idCommande;

IF v_typeCom = 1 THEN
    UPDATE commande
    SET totalTTC = v_totalHT * 1.055;
ELSE
    UPDATE commande
    SET totalTTC = v_totalHT * 1.1;
END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_ligne_insert` BEFORE INSERT ON `lignedecommande` FOR EACH ROW BEGIN
DECLARE v_prixHT decimal(15,2) ;
SET v_prixHT = 0.0 ;

SELECT prixProduitHT INTO v_prixHT 
FROM produit WHERE idProduit = new.idProduit ;

SET new.totalHT = v_prixHT  * new.quantite ;

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_ligne_update` BEFORE UPDATE ON `lignedecommande` FOR EACH ROW BEGIN

DECLARE v_prixHT decimal(15,2) ;
SET v_prixHT = 0.0 ;

SELECT prixProduitHT INTO v_prixHT 
FROM produit WHERE idProduit = new.idProduit ;

SET new.totalHT = v_prixHT  * new.quantite ;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

CREATE TABLE `produit` (
  `idProduit` int(11) NOT NULL,
  `libProduit` varchar(255) DEFAULT NULL,
  `prixProduitHT` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `produit`
--

INSERT INTO `produit` (`idProduit`, `libProduit`, `prixProduitHT`) VALUES
(1, 'Pizza Margherita', 8.50),
(2, 'Pizza Quattro Stagioni', 12.00),
(3, 'Pizza Pepperoni', 10.50),
(4, 'Pizza Hawaienne', 11.00),
(5, 'Pizza Calzone', 13.50),
(6, 'Pizza Végétarienne', 11.50),
(7, 'Pizza Quatre Fromages', 12.50),
(8, 'Pizza Chorizo', 13.00),
(9, 'Pizza Saumon Fumé', 15.00),
(10, 'Pizza Bolognaise', 12.00),
(11, 'Pizza Thon', 10.00),
(12, 'Pizza Chèvre Miel', 13.50),
(13, 'Pizza Orientale', 14.00),
(14, 'Pizza Paysanne', 12.50),
(15, 'Pizza Regina', 11.50);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `idUtilisateur` int(11) NOT NULL,
  `loginUtil` varchar(255) DEFAULT NULL,
  `emailUtil` varchar(255) DEFAULT NULL,
  `mdpUtil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`idUtilisateur`, `loginUtil`, `emailUtil`, `mdpUtil`) VALUES
(1, '123', '123@gmai.com', '$2y$10$eQB0bNABIobXJDd4cVIIROioNR5BWVIeTO49zUTZr04FRMKNxiXnm');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`idCommande`),
  ADD KEY `idEtat` (`idEtat`),
  ADD KEY `idUtilisateur` (`idUtilisateur`);

--
-- Index pour la table `etat`
--
ALTER TABLE `etat`
  ADD PRIMARY KEY (`idEtat`);

--
-- Index pour la table `lignedecommande`
--
ALTER TABLE `lignedecommande`
  ADD PRIMARY KEY (`idCommande`,`idProduit`),
  ADD KEY `idProduit` (`idProduit`);

--
-- Index pour la table `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`idProduit`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`idUtilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `idCommande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `etat`
--
ALTER TABLE `etat`
  MODIFY `idEtat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `produit`
--
ALTER TABLE `produit`
  MODIFY `idProduit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `idUtilisateur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`idEtat`) REFERENCES `etat` (`idEtat`),
  ADD CONSTRAINT `commande_ibfk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateur` (`idUtilisateur`);

--
-- Contraintes pour la table `lignedecommande`
--
ALTER TABLE `lignedecommande`
  ADD CONSTRAINT `lignedecommande_ibfk_1` FOREIGN KEY (`idCommande`) REFERENCES `commande` (`idCommande`),
  ADD CONSTRAINT `lignedecommande_ibfk_2` FOREIGN KEY (`idProduit`) REFERENCES `produit` (`idProduit`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
