-- 2. Creazione Tabella Room
CREATE TABLE room (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    nome VARCHAR(50) NOT NULL,
    piano INT NOT NULL,
    CONSTRAINT fk_room_building 
        FOREIGN KEY (building_id) REFERENCES building(id) ON DELETE CASCADE,
    CONSTRAINT uq_building_room 
        UNIQUE (building_id, nome)
);

-- 4. Creazione Tabella Issue
CREATE TABLE issue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    room_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL,
    titolo VARCHAR(150) NOT NULL,
    descrizione TEXT NOT NULL,
    data_apertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_chiusura TIMESTAMP NULL DEFAULT NULL,
    stato ENUM('aperto', 'in lavorazione', 'chiuso') DEFAULT 'aperto',
    
    CONSTRAINT fk_issue_user 
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE SET NULL,
    CONSTRAINT fk_issue_room 
        FOREIGN KEY (room_id) REFERENCES room(id) ON DELETE CASCADE,
    CONSTRAINT fk_issue_tecnico 
        FOREIGN KEY (tecnico_id) REFERENCES user(id) ON DELETE SET NULL,
        
    CONSTRAINT chk_date_issue 
        CHECK (data_chiusura IS NULL OR data_chiusura >= data_apertura)
);