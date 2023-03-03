-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 15 déc. 2022 à 16:14
-- Version du serveur : 10.4.27-MariaDB
-- Version de PHP : 8.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `hopital_php`
--

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `Chemin` varchar(550) NOT NULL,
  `Nom_doc` varchar(150) DEFAULT NULL,
  `Nature` int(11) DEFAULT NULL,
  `Contenu` varchar(100) DEFAULT NULL,
  `Patient` int(11) DEFAULT NULL,
  `Extension` varchar(10) DEFAULT NULL,
  `Taille` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`Chemin`, `Nom_doc`, `Nature`, `Contenu`, `Patient`, `Extension`, `Taille`) VALUES
('fichiers_patients/Patient1/Documents/Patient1_Prescriptions_SY-Omar_Fiche patient.pdf', 'SY-Omar', 2, 'Fiche patient', 1, 'pdf', 7497),
('fichiers_patients/Patient5/Documents/Patient5_Pièces d\'identité_Carte vitale_Carte Vital .jpg', 'Carte vitale', 3, 'Carte Vital ', 5, 'jpg', 30155),
('fichiers_patients/Patient6/Documents/Patient6_Prescriptions_CASSEL-Vincent_Fiche patient.pdf', 'CASSEL-Vincent', 2, 'Fiche patient', 6, 'pdf', 7499),
('fichiers_patients/Patient7/Documents/Patient7_Ordonnances_ordonnance01_Ordonnance .jpg', 'ordonnance01', 1, 'Ordonnance ', 2, 'jpg', 14149);

-- --------------------------------------------------------

--
-- Structure de la table `motifs`
--

CREATE TABLE `motifs` (
  `Code_m` int(11) NOT NULL,
  `Libelle_motif` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `motifs`
--

INSERT INTO `motifs` (`Code_m`, `Libelle_motif`) VALUES
(1, 'Consultation libre'),
(2, 'Urgence'),
(3, 'Prescription');

-- --------------------------------------------------------

--
-- Structure de la table `natures`
--

CREATE TABLE `natures` (
  `Code_n` int(11) NOT NULL,
  `Libelle_nature` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `natures`
--

INSERT INTO `natures` (`Code_n`, `Libelle_nature`) VALUES
(1, 'Ordonnances'),
(2, 'Prescriptions'),
(3, 'Pièces d\'identité');

-- --------------------------------------------------------

--
-- Structure de la table `patients`
--

CREATE TABLE `patients` (
  `Code_patient` int(11) NOT NULL,
  `Nom` varchar(50) DEFAULT NULL,
  `Prenom` varchar(50) DEFAULT NULL,
  `Sexe` varchar(1) DEFAULT NULL,
  `Date_naiss` date DEFAULT NULL,
  `Num_secu` varchar(15) DEFAULT NULL,
  `Code_pays` varchar(3) DEFAULT NULL,
  `Date_entree` date DEFAULT NULL,
  `Code_motif` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `patients`
--

INSERT INTO `patients` (`Code_patient`, `Nom`, `Prenom`, `Sexe`, `Date_naiss`, `Num_secu`, `Code_pays`, `Date_entree`, `Code_motif`) VALUES
(1, 'SY', 'Omar', 'M', '1978-01-20', '178017830240455', 'FR', '2022-02-01', 1),
(2, 'DEPARDIEU', 'Gérard', 'M', '1948-12-27', '148127504406759', 'FR', '2022-04-05', 2),
(3, 'DUJARDIN', 'Jean', 'M', '1972-06-19', '172065903800855', 'FR', '2022-12-06', 3),
(4, 'RENO', 'Jean', 'M', '1948-07-30', '', 'MA', '2018-08-18', 1),
(5, 'COTILLARD', 'Marion', 'F', '1975-09-30', '275097503200542', 'FR', '2018-09-26', 1),
(6, 'CASSEL', 'Vincent', 'M', '1966-11-23', '166117500600711', 'FR', '2023-01-01', 3),
(7, 'GREEN', 'Eva', 'F', '1980-06-17', '280067500400733', 'FR', '2022-11-15', 2),
(8, 'EFIRA', 'Virginie', 'F', '1977-05-05', '', 'BE', '2022-10-30', 2);

-- --------------------------------------------------------

--
-- Structure de la table `pays`
--

CREATE TABLE `pays` (
  `Code_p` varchar(3) NOT NULL,
  `Libelle_pays` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `pays`
--

INSERT INTO `pays` (`Code_p`, `Libelle_pays`) VALUES
('BE', 'Belgique'),
('DZ', 'Algérie'),
('FR', 'France'),
('MA', 'Maroc'),
('TN', 'Tunisie');

-- --------------------------------------------------------

--
-- Structure de la table `sexe`
--

CREATE TABLE `sexe` (
  `Code_s` varchar(1) NOT NULL,
  `Libelle_sexe` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sexe`
--

INSERT INTO `sexe` (`Code_s`, `Libelle_sexe`) VALUES
('F', 'Féminin'),
('M', 'Masculin');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`Chemin`),
  ADD KEY `Nature` (`Nature`),
  ADD KEY `Patient` (`Patient`);

--
-- Index pour la table `motifs`
--
ALTER TABLE `motifs`
  ADD PRIMARY KEY (`Code_m`);

--
-- Index pour la table `natures`
--
ALTER TABLE `natures`
  ADD PRIMARY KEY (`Code_n`);

--
-- Index pour la table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`Code_patient`),
  ADD KEY `Sexe` (`Sexe`),
  ADD KEY `Code_pays` (`Code_pays`),
  ADD KEY `Code_motif` (`Code_motif`);

--
-- Index pour la table `pays`
--
ALTER TABLE `pays`
  ADD PRIMARY KEY (`Code_p`);

--
-- Index pour la table `sexe`
--
ALTER TABLE `sexe`
  ADD PRIMARY KEY (`Code_s`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `motifs`
--
ALTER TABLE `motifs`
  MODIFY `Code_m` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `natures`
--
ALTER TABLE `natures`
  MODIFY `Code_n` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `patients`
--
ALTER TABLE `patients`
  MODIFY `Code_patient` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`Nature`) REFERENCES `natures` (`Code_n`),
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`Patient`) REFERENCES `patients` (`Code_patient`);

--
-- Contraintes pour la table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`Sexe`) REFERENCES `sexe` (`Code_s`),
  ADD CONSTRAINT `patients_ibfk_2` FOREIGN KEY (`Code_pays`) REFERENCES `pays` (`Code_p`),
  ADD CONSTRAINT `patients_ibfk_3` FOREIGN KEY (`Code_motif`) REFERENCES `motifs` (`Code_m`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
