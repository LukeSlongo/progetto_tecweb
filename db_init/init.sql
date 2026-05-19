-- 1. Tabella Dipartimenti
CREATE TABLE IF NOT EXISTS dipartimento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- 2. Tabella Edifici
CREATE TABLE IF NOT EXISTS edificio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    indirizzo VARCHAR(255) NOT NULL,
    dipartimento_id INT NOT NULL,
    FOREIGN KEY (dipartimento_id) REFERENCES dipartimento(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 3. Tabella Aule
CREATE TABLE IF NOT EXISTS aula (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    edificio_id INT NOT NULL,
    piano INT NOT NULL,
    FOREIGN KEY (edificio_id) REFERENCES edificio(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 4. Tabella Utenti (Unificata)
CREATE TABLE IF NOT EXISTS utente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Spazio abbondante per gli hash di PHP (password_hash)
    ruolo ENUM('studente', 'tecnico', 'admin') DEFAULT 'studente',
    dipartimento_id INT NULL, -- NULL per gli studenti, valorizzato per tecnici/admin
    FOREIGN KEY (dipartimento_id) REFERENCES dipartimento(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- 5. Tabella Segnalazioni
CREATE TABLE IF NOT EXISTS segnalazione (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT NOT NULL,
    stato ENUM('aperto', 'in_carico', 'risolto', 'chiuso') DEFAULT 'aperto',
    priorita ENUM('bassa', 'media', 'alta') DEFAULT 'media',
    data_apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_chiusura TIMESTAMP NULL DEFAULT NULL,
    utente_id INT NOT NULL, -- Chi apre la segnalazione
    tecnico_id INT NULL,    -- Chi la prende in carico (inizialmente NULL)
    aula_id INT NOT NULL,   -- Dove si trova il guasto
    FOREIGN KEY (utente_id) REFERENCES utente(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES utente(id) ON DELETE SET NULL,
    FOREIGN KEY (aula_id) REFERENCES aula(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Tabella Ponte: Preferiti (Relazione Molti-a-Molti tra Studenti e Aule)
CREATE TABLE IF NOT EXISTS preferiti (
    utente_id INT NOT NULL,
    aula_id INT NOT NULL,
    PRIMARY KEY (utente_id, aula_id), -- Chiave primaria composta per evitare duplicati
    FOREIGN KEY (utente_id) REFERENCES utente(id) ON DELETE CASCADE,
    FOREIGN KEY (aula_id) REFERENCES aula(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==========================================================
-- INSERIMENTO DATI DI TEST (Popolamento iniziale)
-- ==========================================================

-- Dipartimenti
INSERT INTO dipartimento (id, nome) VALUES 
(1, 'Informatica e Matematica'),
(2, 'Giurisprudenza');

-- Edifici (collegati ai dipartimenti)
INSERT INTO edificio (id, nome, indirizzo, dipartimento_id) VALUES
(1, 'Blocco A', 'Via Università 10, Edificio Nord', 1),
(2, 'Blocco B', 'Via Università 12, Edificio Est', 1),
(3, 'Sede Centrale Gius', 'Via Roma 45', 2);

-- Aule (collegate agli edifici)
INSERT INTO aula (nome, edificio_id, piano) VALUES 
('Aula Alfa', 1, 0),          -- Blocco A (Informatica)
('Laboratorio Turing', 2, 2),  -- Blocco B (Informatica)
('Aula Magna Gius', 3, 1);     -- Sede Centrale (Giurisprudenza)

-- Utenti
-- Nota: la password di test è sempre l'hash di 'password123'
INSERT INTO utente (id, nome, cognome, email, password, ruolo, dipartimento_id) VALUES 
(1, 'Mario', 'Rossi', 'mario@studente.it', 'mario', 'studente', NULL),
(2, 'Luigi', 'Verdi', 'luigi@tecnico.it', 'luigi', 'tecnico', 1),  -- Lavora a Informatica
(3, 'Anna', 'Bianchi', 'anna@admin.it', 'anna', 'admin', 1);

-- Segnalazioni di prova
-- La prima è aperta (data_chiusura e tecnico_id sono NULL di default)
INSERT INTO segnalazione (titolo, descrizione, stato, priorita, utente_id, tecnico_id, aula_id) VALUES 
('Proiettore non si accende', 'Il proiettore dell Aula Alfa non riceve segnale HDMI.', 'aperto', 'alta', 1, NULL, 1);

-- La seconda è già in carico al tecnico Luigi (id: 2), data_chiusura rimane NULL
INSERT INTO segnalazione (titolo, descrizione, stato, priorita, utente_id, tecnico_id, aula_id) VALUES 
('PC n.5 bloccato', 'Schermata blu fissa sul PC della quinta fila nel laboratorio.', 'in_carico', 'media', 1, 2, 2);