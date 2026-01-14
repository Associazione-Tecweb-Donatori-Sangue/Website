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
-- 3. Struttura della tabella `donazioni`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `lista_prenotazioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `data_prenotazione` date NOT NULL,
  `ora_prenotazione` time NOT NULL,
  `nome_sede` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_prenotazione_utente` (`user_id`),
  CONSTRAINT `fk_prenotazione_utente` FOREIGN KEY (`user_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento prenotazioni
INSERT INTO `lista_prenotazioni` (`id`, `user_id`, `username`, `data_prenotazione`, `ora_prenotazione`, `nome_sede`) VALUES
(1, 1, 'user', '2026-01-10', '09:00:00', 'ATDS Piovego'),
(2, 1, 'user', '2026-01-25', '10:00:00', 'ATDS Lum250'),
(3, 1, 'user', '2026-02-15', '11:30:00', 'ATDS Stanga'),
(4, 1, 'user', '2026-03-01', '14:00:00', 'ATDS Portello'),
(5, 1, 'user', '2026-03-20', '09:30:00', 'ATDS Prato della Valle'),
(6, 1, 'user', '2026-04-05', '15:00:00', 'ATDS Dietro Stazione'),
(7, 1, 'user', '2026-04-28', '10:30:00', 'ATDS del Bo'),
(8, 1, 'user', '2026-05-15', '11:00:00', 'ATDS Specola'),
(9, 1, 'user', '2026-06-08', '09:00:00', 'ATDS Piovego'),
(10, 1, 'user', '2026-06-25', '14:30:00', 'ATDS Lum250'),
(11, 3, 'prova', '2026-01-15', '09:00:00', 'ATDS Piovego'),
(12, 4, 'diana', '2026-01-20', '10:30:00', 'ATDS Lum250'),
(13, 3, 'prova', '2026-02-10', '11:00:00', 'ATDS Stanga'),
(14, 4, 'diana', '2026-02-25', '14:00:00', 'ATDS Portello'),
(15, 3, 'prova', '2026-03-05', '09:30:00', 'ATDS Prato della Valle'),
(16, 4, 'diana', '2026-03-18', '15:30:00', 'ATDS Dietro Stazione'),
(17, 3, 'prova', '2026-04-12', '10:00:00', 'ATDS del Bo'),
(18, 4, 'diana', '2026-04-22', '11:30:00', 'ATDS Specola'),
(19, 3, 'prova', '2026-05-08', '09:00:00', 'ATDS Piovego'),
(20, 4, 'diana', '2026-05-30', '14:30:00', 'ATDS Lum250'),
(21, 3, 'prova', '2026-06-14', '10:30:00', 'ATDS Stanga'),
(22, 4, 'diana', '2026-06-28', '16:00:00', 'ATDS Portello'),
(23, 3, 'prova', '2026-07-10', '09:00:00', 'ATDS Prato della Valle'),
(24, 4, 'diana', '2026-07-25', '11:00:00', 'ATDS Dietro Stazione'),
(25, 3, 'prova', '2026-08-15', '10:00:00', 'ATDS del Bo'),
(26, 4, 'diana', '2026-08-29', '14:00:00', 'ATDS Specola'),
(27, 3, 'prova', '2026-09-12', '09:30:00', 'ATDS Piovego'),
(28, 4, 'diana', '2026-09-26', '15:00:00', 'ATDS Lum250'),
(29, 3, 'prova', '2026-10-18', '10:00:00', 'ATDS Stanga'),
(30, 4, 'diana', '2026-10-30', '11:30:00', 'ATDS Portello');

-- --------------------------------------------------------
-- 4. Struttura della tabella `sedi`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sedi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  `indirizzo` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `link_maps` varchar(500) NOT NULL,
  `telefono` varchar(20) DEFAULT '+39 0123456789',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserimento dati per la tabella `sedi`
INSERT INTO `sedi` (`id`, `nome`, `immagine`, `indirizzo`, `descrizione`, `link_maps`, `telefono`) VALUES
(1, 'ATDS Piovego', 'images/sede_piovego.jpg', 'Viale Giuseppe Colombo, 1, 35131 Padova PD', 'Piccola struttura nel quartiere Piovego, facilmente raggiungibile, con personale qualificato e orari flessibili per le donazioni.', 'https://maps.app.goo.gl/vEC15q9E76uzmeAt6', '+39 0123456789'),
(2, 'ATDS Lum250', 'images/sede_lum250.jpg', 'Via Luigi Luzzatti, 8, 35121 Padova PD', 'Piccolo punto donazioni vicino allAula LuM250, comodo soprattutto per studenti e personale universitario.', 'https://maps.app.goo.gl/fH1NsTRULSp1PnAG6', '+39 0123456789'),
(3, 'ATDS Stanga', 'images/sede_stanga.jpg', 'Piazzale Stanga, 35131 Padova PD', 'Punto donazioni nel bel mezzo della famosa Stanga, con un ambiente accogliente e pulito, e personale disponibile per ogni esigenza.', 'https://maps.app.goo.gl/4iB8YhxApCZcrsM88', '+39 0123456789'),
(4, 'ATDS Portello', 'images/sede_portello.jpg', 'Via Giovanni Gradenigo, Via del Portello, 35131 Padova PD', 'Punto donazioni nella zona del Portello, appena rinnovato, con facile accesso e un ambiente confortevole per i donatori.', 'https://maps.app.goo.gl/o6FZ4fJ1Bzae7RTn6', '+39 0123456789'),
(5, 'ATDS Prato della Valle', 'images/sede_prato.jpg', 'Prato della Valle, 35141 Padova PD', 'Sede principale e punto donazioni in una delle piazze più belle di Padova, con un ambiente accogliente e personale disponibile.', 'https://maps.app.goo.gl/3kNyunAYoqmqTya58', '+39 0123456789'),
(6, 'ATDS Dietro Stazione', 'images/sede_dietro_stazione.jpg', 'Via Jacopo d\'Avanzo, 23, 35132 Padova PD', 'Punto donazioni situato dietro la stazione di Padova, comodo per chi arriva in treno e desidera donare sangue in modo rapido, semplice e pulito.', 'https://maps.app.goo.gl/SVBms5SxPz9xDRF66', '+39 0123456789'),
(7, 'ATDS del Bo', 'images/sede_bo.jpg', 'Via VIII Febbraio, 2, 35122 Padova PD', 'Punto donazioni situato nello storico Palazzo del Bo, cuore dell\'Università di Padova. Immerso nella storia, ideale per chi desidera donare sangue in un ambiente unico e ricco di cultura.', 'https://maps.app.goo.gl/ZfPyJfsDZELLHR569', '+39 0123456789'),
(8, 'ATDS Specola', 'images/sede_specola.jpg', 'Vicolo dell\'Osservatorio, 5, 35122 Padova PD', 'Suggestivo punto donazioni situato sulla torre accanto all\'Osservatorio Astronomico di Padova, ideale per chi è amante del brivido e non si limita a donare in tranquillità.', 'https://maps.app.goo.gl/tE6r3TuhKiPFextF8', '+39 0123456789');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;