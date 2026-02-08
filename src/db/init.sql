USE ggiora;

-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Creato il: Gen 17, 2026 alle 15:00
-- Versione del server: 11.8.3-MariaDB-ubu2404
-- Versione PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `miodb`
--

-- --------------------------------------------------------

-- Struttura della tabella `utenti`
CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `ruolo` enum('user','admin') NOT NULL DEFAULT 'user',
  `foto_profilo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dump dei dati per la tabella `utenti`
INSERT INTO `utenti` (`id`, `username`, `password`, `ruolo`, `foto_profilo`) VALUES
(1, 'user', '$2y$10$5oYlh4Kof9s1YqQy6pl2cOksZIcfK6Gfd9OOK.cDFfjwfbVK7IfcO', 'user', 'profile_1_696b6c61663ef.jpg'),
(2, 'admin', '$2y$10$OyRC0xNC8fy8o5tMJbldquZ/1GwdVazcuEdukp51VJiBxMsL.chwi', 'admin', 'profile_2_696b724c9062a.jpg'),
(3, 'prova', '$2y$12$kiiw9Bl3saOuzk5JDUTo5e2pd5vb2KRlWjUawYtNIJdegcoSZwTky', 'user', NULL),
(4, 'diana', '$2y$12$SZIc7eiimwQPUfX.zOfWo.VEyPGeoibD/tk1bE1.rZUgcerojVIx.', 'user', NULL),
(5, 'test', '$2a$12$1Jw.uStuH0INnQvknJ5cAu0OUYt0M5Pdc7XT6v5l348ZN.ma.pKFG', 'user', NULL),
(6, 'giaco', '$2a$12$TKTx2Siuueh50uwD3Zlu7epbKsxJmmPyzUSk0jLnr176HxQiDK79y', 'user', NULL),
(7, 'minus', '$2a$12$2PsKyinVraG2t7M.lBr7Uu5KngM39A/imteBI9eXGxctYVGdrWsCG', 'user', NULL),
(8, 'prova2', '$2a$12$bKho7vn7YrFQ7nqxwSJf9uNDPRzh7kHHpwZlDrTByB4I259g6pzRm', 'user', NULL),
(9, 'nuovo', '$2a$12$Lg7yantqUdEPV2FArn//nO/xR3kS/35FDDh9vUWHZipyJPl5m/AwS', 'user', NULL),
(10, 'xG42', '$2a$12$Gb3sLNnBKcjbfrOiNulrpeQdd/QNkrG.vpUOlE9Ueh3HaLfntQypW', 'user', NULL);

-- --------------------------------------------------------

-- Struttura della tabella `donatori`
CREATE TABLE `donatori` (
  `user_id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cognome` varchar(50) NOT NULL,
  `data_nascita` date NOT NULL,
  `luogo_nascita` varchar(100) NOT NULL,
  `codice_fiscale` varchar(16) NOT NULL,
  `indirizzo` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `gruppo_sanguigno` varchar(12) NOT NULL,
  `sesso` varchar(20) NOT NULL,
  `peso` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dump dei dati per la tabella `donatori`
INSERT INTO `donatori` (`user_id`, `nome`, `cognome`, `data_nascita`, `luogo_nascita`, `codice_fiscale`, `indirizzo`, `telefono`, `email`, `gruppo_sanguigno`, `sesso`, `peso`) VALUES
(1, 'Maria', 'Rossi', '1990-05-15', 'Milano', 'RSSMRA90E15F205X', 'Via Roma 1, Milano', '+39 1234567890', 'user@user.com', 'A+', 'Femmina', 62.5),
(4, 'Diana', 'Georgescu', '1983-07-15', 'Teolo', 'DGMRA90E15F205X', 'Via Cristoforo Colombo 4, Padova', '+39 1234567890', 'diana@gmail.com', 'A+', 'Femmina', 63.8),
(7, 'Manuel', 'Dos Santos', '1999-01-21', 'Rio de Janeiro', 'MDSRA90E15F205X', 'Via Luzzatti 3, Padova', '+39 1234567890', 'manu@virgilio.com', '0-', 'Maschio', 74),
(9, 'Lucia', 'Bianchi', '2007-11-5', 'Roma', 'LBCMRA90E15F205X', 'Via Piovega Sud 97, Villanova di Camposampiero', '+39 1234567890', 'luci@yahoo.it', 'AB+', 'Femmina', 80.5);


-- --------------------------------------------------------

-- Struttura della tabella `lista_prenotazioni`
CREATE TABLE `lista_prenotazioni` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sede_id` int(11) NOT NULL,
  `data_prenotazione` date NOT NULL,
  `ora_prenotazione` varchar(5) NOT NULL,
  `tipo_donazione` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dump dei dati per la tabella `lista_prenotazioni`
INSERT INTO `lista_prenotazioni` (`id`, `user_id`, `sede_id`, `data_prenotazione`, `ora_prenotazione`, `tipo_donazione`) VALUES
(1, 1, 1, '2025-03-15', '09:30', 'Plasma'),
(2, 4, 3, '2025-04-10', '10:00', 'Sangue intero'),
(3, 7, 2, '2025-05-22', '08:30', 'Sangue intero'),
(4, 9, 5, '2025-06-18', '14:00', 'Sangue intero'),
(5, 1, 4, '2025-07-08', '11:30', 'Piastrine'),
(6, 7, 6, '2025-08-14', '09:00', 'Sangue intero'),
(7, 4, 1, '2025-09-05', '15:30', 'Plasma'),
(8, 9, 7, '2025-10-12', '10:30', 'Sangue intero'),
(9, 1, 8, '2025-11-20', '08:00', 'Sangue intero'),
(10, 7, 3, '2025-12-03', '16:00', 'Plasma'),
(11, 4, 2, '2026-01-14', '09:30', 'Sangue intero'),
(12, 9, 4, '2026-01-28', '14:30', 'Plasma'),
(13, 1, 5, '2026-02-10', '10:30', 'Plasma'),
(14, 7, 1, '2026-02-25', '08:30', 'Sangue intero'),
(15, 4, 6, '2026-03-12', '11:00', 'Piastrine'),
(16, 9, 8, '2026-03-20', '15:00', 'Sangue intero'),
(17, 1, 3, '2026-04-08', '09:00', 'Sangue intero'),
(18, 7, 7, '2026-05-15', '14:00', 'Piastrine'),
(19, 4, 4, '2026-06-22', '10:30', 'Sangue intero'),
(20, 9, 2, '2026-06-30', '08:30', 'Plasma'),
(21, 1, 1, '2026-07-18', '16:30', 'Plasma'),
(22, 7, 5, '2026-08-10', '09:30', 'Sangue intero'),
(23, 4, 8, '2026-09-25', '11:30', 'Sangue intero'),
(24, 9, 6, '2026-10-14', '14:30', 'Piastrine'),
(25, 1, 7, '2026-11-05', '10:00', 'Sangue intero'),
(26, 7, 3, '2026-12-18', '15:30', 'Sangue intero');

-- --------------------------------------------------------

-- Struttura della tabella `sedi`
CREATE TABLE `sedi` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `immagine` varchar(255) NOT NULL,
  `indirizzo` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `link_maps` varchar(500) NOT NULL,
  `telefono` varchar(20) DEFAULT '+39 0123456789'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- Dump dei dati per la tabella `sedi`
INSERT INTO `sedi` (`id`, `nome`, `immagine`, `indirizzo`, `descrizione`, `link_maps`, `telefono`) VALUES
(1, 'ATDS Piovego', 'images/sede_piovego.jpg', 'Viale Giuseppe Colombo, 1, 35131 Padova PD', 'Piccola struttura nel quartiere Piovego, facilmente raggiungibile, con personale qualificato e orari flessibili per le donazioni.', 'https://maps.app.goo.gl/vEC15q9E76uzmeAt6', '+39 0123456789'),
(2, 'ATDS Lum250', 'images/sede_lum250.jpg', 'Via Luigi Luzzatti, 8, 35121 Padova PD', 'Piccolo punto donazioni vicino all''Aula LuM250, comodo soprattutto per studenti e personale universitario per donare fra una lezione e l''altra.', 'https://maps.app.goo.gl/fH1NsTRULSp1PnAG6', '+39 0123456789'),
(3, 'ATDS Stanga', 'images/sede_stanga.jpg', 'Piazzale Stanga, 35131 Padova PD', 'Punto donazioni nel bel mezzo della famosa Stanga, con un ambiente accogliente e pulito, e personale disponibile per ogni esigenza.', 'https://maps.app.goo.gl/4iB8YhxApCZcrsM88', '+39 0123456789'),
(4, 'ATDS Portello', 'images/sede_portello.jpg', 'Via Giovanni Gradenigo, Via del Portello, 35131 Padova PD', 'Punto donazioni nella zona del Portello, appena rinnovato, con facile accesso e un ambiente confortevole per i donatori.', 'https://maps.app.goo.gl/o6FZ4fJ1Bzae7RTn6', '+39 0123456789'),
(5, 'ATDS Prato della Valle', 'images/sede_prato.jpg', 'Prato della Valle, 35141 Padova PD', 'Sede principale e punto donazioni in una delle piazze piu belle di Padova, con un ambiente accogliente e personale disponibile.', 'https://maps.app.goo.gl/3kNyunAYoqmqTya58', '+39 0123456789'),
(6, 'ATDS Dietro Stazione', 'images/sede_dietro_stazione.jpg', 'Via Jacopo d''Avanzo, 23, 35132 Padova PD', 'Punto donazioni situato dietro la stazione di Padova, comodo per chi arriva in treno e desidera donare sangue in modo rapido, semplice e pulito.', 'https://maps.app.goo.gl/SVBms5SxPz9xDRF66', '+39 0123456789'),
(7, 'ATDS del Bo', 'images/sede_bo.jpg', 'Via VIII Febbraio, 2, 35122 Padova PD', 'Punto donazioni situato nello storico Palazzo del Bo, cuore dell''Universita di Padova. Immerso nella storia, ideale per chi desidera donare sangue in un ambiente unico e ricco di cultura.', 'https://maps.app.goo.gl/ZfPyJfsDZELLHR569', '+39 0123456789'),
(8, 'ATDS Specola', 'images/sede_specola.jpg', 'Vicolo dell''Osservatorio, 5, 35122 Padova PD', 'Suggestivo punto donazioni situato sulla torre accanto all''Osservatorio Astronomico di Padova, ideale per chi e amante del brivido e non si limita a donare in tranquillita.', 'https://maps.app.goo.gl/tE6r3TuhKiPFextF8', '+39 0123456789');

-- --------------------------------------------------------

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `donatori`
--
ALTER TABLE `donatori`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `codice_fiscale` (`codice_fiscale`);

--
-- Indici per le tabelle `lista_prenotazioni`
--
ALTER TABLE `lista_prenotazioni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sede_id` (`sede_id`),
  ADD KEY `idx_prenotazioni_data` (`data_prenotazione`, `ora_prenotazione`),
  ADD KEY `idx_prenotazioni_sede_data` (`sede_id`, `data_prenotazione`);

--
-- Indici per le tabelle `sedi`
--
ALTER TABLE `sedi`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `lista_prenotazioni`
--
ALTER TABLE `lista_prenotazioni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `sedi`
--
ALTER TABLE `sedi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `donatori`
--
ALTER TABLE `donatori`
  ADD CONSTRAINT `fk_donatore_utente` FOREIGN KEY (`user_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `lista_prenotazioni`
--
ALTER TABLE `lista_prenotazioni`
  ADD CONSTRAINT `lista_prenotazioni_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lista_prenotazioni_ibfk_2` FOREIGN KEY (`sede_id`) REFERENCES `sedi` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;