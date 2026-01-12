-- Disabilita controlli chiavi esterne per evitare errori durante la creazione
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Struttura della tabella `utenti`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `utenti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ruolo` enum('user','admin') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento utenti base (password: 'user', 'admin', ecc.)
INSERT INTO `utenti` (`id`, `username`, `password`, `ruolo`) VALUES
(1, 'user', '$2y$10$5oYlh4Kof9s1YqQy6pl2cOksZIcfK6Gfd9OOK.cDFfjwfbVK7IfcO', 'user'),
(2, 'admin', '$2y$10$OyRC0xNC8fy8o5tMJbldquZ/1GwdVazcuEdukp51VJiBxMsL.chwi', 'admin'),
(3, 'prova', '$2y$12$kiiw9Bl3saOuzk5JDUTo5e2pd5vb2KRlWjUawYtNIJdegcoSZwTky', 'user'),
(4, 'diana', '$2y$12$SZIc7eiimwQPUfX.zOfWo.VEyPGeoibD/tk1bE1.rZUgcerojVIx.', 'user');

-- --------------------------------------------------------
-- 2. Struttura della tabella `donatori`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `donatori` (
  `user_id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `data_nascita` date NOT NULL,
  `luogo_nascita` varchar(100) NOT NULL,
  `codice_fiscale` varchar(16) NOT NULL,
  `indirizzo` varchar(255) NOT NULL, -- Nota: corrisponde a $_POST['residenza']
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gruppo_sanguigno` varchar(5) NOT NULL,
  `sesso` varchar(20) NOT NULL,      -- Modificato da ENUM a VARCHAR per sicurezza
  `peso` float NOT NULL,             -- Modificato da INT a FLOAT
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `codice_fiscale` (`codice_fiscale`),
  CONSTRAINT `fk_donatore_utente` FOREIGN KEY (`user_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Struttura della tabella `sedi`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sedi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `indirizzo` varchar(255) NOT NULL,
  `link_maps` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT '+39 0123456789',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dati per la tabella `sedi`
INSERT INTO `sedi` (`id`, `nome`, `immagine`, `descrizione`, `indirizzo`, `link_maps`, `telefono`) VALUES
(1, 'ATDS Piovego', 'images/sede_piovego.jpg', 'Piccola struttura nel quartiere Piovego...', 'Viale Giuseppe Colombo, 1, 35131 Padova PD', 'https://maps.google.com/?q=Viale+Giuseppe+Colombo+1+Padova', '+39 0123456789'),
(2, 'ATDS Lum250', 'images/sede_lum250.jpg', 'Piccolo punto donazioni vicino all\'Aula LuM250...', 'Via Luigi Luzzatti, 8, 35121 Padova PD', 'https://maps.google.com/?q=Via+Luigi+Luzzatti+8+Padova', '+39 0123456789'),
(3, 'ATDS Stanga', 'images/sede_stanga.jpg', 'Punto donazioni nel bel mezzo della famosa Stanga...', 'Piazzale Stanga, 35131 Padova PD', 'https://maps.google.com/?q=Piazzale+Stanga+Padova', '+39 0123456789'),
(4, 'ATDS Portello', 'images/sede_portello.jpg', 'Punto donazioni nella zona del Portello...', 'Via Giovanni Gradenigo, Via del Portello, 35131 Padova PD', 'https://maps.google.com/?q=Via+Giovanni+Gradenigo+Padova', '+39 0123456789'),
(5, 'ATDS Prato della Valle', 'images/sede_prato.jpg', 'Sede principale in una delle piazze più belle...', 'Prato della Valle, 35141 Padova PD', 'https://maps.google.com/?q=Prato+della+Valle+Padova', '+39 0123456789'),
(6, 'ATDS Dietro Stazione', 'images/sede_dietro_stazione.jpg', 'Punto donazioni situato dietro la stazione...', 'Via Jacopo d\'Avanzo, 23, 35132 Padova PD', 'https://maps.google.com/?q=Via+Jacopo+dAvanzo+23+Padova', '+39 0123456789');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;