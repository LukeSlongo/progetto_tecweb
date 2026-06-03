-- 1. Creazione Tabella Edificio
CREATE TABLE edificio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL UNIQUE,
    indirizzo VARCHAR(255) NOT NULL
);

-- 2. Creazione Tabella Aula
CREATE TABLE aula (
    id INT AUTO_INCREMENT PRIMARY KEY,
    edificio_id INT NOT NULL,
    nome VARCHAR(50) NOT NULL,
    piano INT NOT NULL,
    CONSTRAINT fk_aula_edificio 
        FOREIGN KEY (edificio_id) REFERENCES edificio(id) ON DELETE RESTRICT,
    CONSTRAINT uq_edificio_aula 
        UNIQUE (edificio_id, nome)
);

-- 3. Creazione Tabella Utente
CREATE TABLE utente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_utente VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    ruolo ENUM('studente', 'tecnico', 'admin') NOT NULL
);

-- 4. Creazione Tabella Segnalazione
CREATE TABLE segnalazione (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utente_id INT NOT NULL,
    aula_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT NOT NULL,
    data_apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_chiusura TIMESTAMP NULL DEFAULT NULL,
    stato ENUM('aperto', 'in lavorazione', 'chiuso') DEFAULT 'aperto',
    
    CONSTRAINT fk_segnalazione_utente 
        FOREIGN KEY (utente_id) REFERENCES utente(id) ON DELETE CASCADE,
    CONSTRAINT fk_segnalazione_aula 
        FOREIGN KEY (aula_id) REFERENCES aula(id) ON DELETE RESTRICT,
    CONSTRAINT fk_segnalazione_tecnico 
        FOREIGN KEY (tecnico_id) REFERENCES utente(id) ON DELETE SET NULL,
        
    CONSTRAINT chk_date_segnalazione 
        CHECK (data_chiusura IS NULL OR data_chiusura >= data_apertura)
);

-- 5. Creazione Tabella Preferiti
CREATE TABLE preferiti (
    utente_id INT NOT NULL,
    aula_id INT NOT NULL,
    PRIMARY KEY (utente_id, aula_id),
    CONSTRAINT fk_preferiti_utente 
        FOREIGN KEY (utente_id) REFERENCES utente(id) ON DELETE CASCADE,
    CONSTRAINT fk_preferiti_aula 
        FOREIGN KEY (aula_id) REFERENCES aula(id) ON DELETE CASCADE
);

-- 6. Trigger per la rimozione del Tecnico
DELIMITER //

CREATE TRIGGER trg_rimozione_tecnico
BEFORE DELETE ON utente
FOR EACH ROW
BEGIN
    -- Se l'utente eliminato è un tecnico, sgancia le segnalazioni in lavorazione
    -- e riportale allo stato 'aperto'.
    IF OLD.ruolo = 'tecnico' THEN
        UPDATE segnalazione
        SET stato = 'aperto', tecnico_id = NULL
        WHERE tecnico_id = OLD.id AND stato = 'in lavorazione';
    END IF;
END;
//

DELIMITER ;