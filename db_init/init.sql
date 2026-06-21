DROP TABLE IF EXISTS favorite;
DROP TABLE IF EXISTS issue;
DROP TABLE IF EXISTS room;
DROP TABLE IF EXISTS building;
DROP TABLE IF EXISTS `user`;

CREATE TABLE building (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    address VARCHAR(255) NOT NULL
);

CREATE TABLE room (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    CONSTRAINT fk_room_building FOREIGN KEY (building_id) REFERENCES building (id) ON DELETE CASCADE,
    CONSTRAINT uq_building_room UNIQUE (building_id, name)
);

CREATE TABLE `user` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'technician', 'admin') NOT NULL
);

CREATE TABLE issue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    room_id INT NOT NULL,
    technician_id INT DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    opened_at DATE DEFAULT (CURRENT_DATE),
    closed_at DATE NULL DEFAULT NULL,
    status ENUM ('open', 'in_progress', 'closed') DEFAULT 'open',
    CONSTRAINT fk_issue_user FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE SET NULL,
    CONSTRAINT fk_issue_room FOREIGN KEY (room_id) REFERENCES room (id) ON DELETE CASCADE,
    CONSTRAINT fk_issue_technician FOREIGN KEY (technician_id) REFERENCES `user` (id) ON DELETE SET NULL,
    CONSTRAINT chk_date_issue CHECK (
        closed_at IS NULL
        OR closed_at >= opened_at
    )
);

CREATE TABLE favorite (
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    PRIMARY KEY (user_id, room_id),
    CONSTRAINT fk_favorite_user 
        FOREIGN KEY (user_id) REFERENCES `user`(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorite_room 
        FOREIGN KEY (room_id) REFERENCES room(id) ON DELETE CASCADE
);

DELIMITER //
-- Trigger che gestisce lo stato di una issue quando viene rimosso un tecnico: una in_progress diventa open
CREATE TRIGGER trg_remove_technician
BEFORE DELETE ON `user`
FOR EACH ROW
BEGIN
    IF OLD.role = 'technician' THEN
        UPDATE issue
        SET status = 'open', technician_id = NULL
        WHERE technician_id = OLD.id AND status = 'in_progress';
    END IF;
END;
//

DELIMITER ;

-- Insert dei ruoli admin e tecnico
INSERT INTO `user` (username, password, role) VALUES 
('admin', '$2y$10$rTwKpKUBtWUq6QQKjVS/FuhEzFASXCpuekO2jsrY16wbaibHU0wtK', 'admin'),
('tecnico', '$2y$10$7nnn3YJsSpqn1O7VnWO50.n/CRCHlMagJEyhQ5TQe9m3J5vh3ihAO', 'technician'),
('studente', '$2y$10$Fk53wBPCRiF3pt7GIhcWMe3UvgEbUsrJ/JqwLe4cDx9VYnnn/lzmO', 'student'),
('studente1', '$2y$10$Fk53wBPCRiF3pt7GIhcWMe3UvgEbUsrJ/JqwLe4cDx9VYnnn/lzmO', 'student'),
('studente2', '$2y$10$Fk53wBPCRiF3pt7GIhcWMe3UvgEbUsrJ/JqwLe4cDx9VYnnn/lzmO', 'student'),
('studente3', '$2y$10$Fk53wBPCRiF3pt7GIhcWMe3UvgEbUsrJ/JqwLe4cDx9VYnnn/lzmO', 'student'),
('studente4', '$2y$10$Fk53wBPCRiF3pt7GIhcWMe3UvgEbUsrJ/JqwLe4cDx9VYnnn/lzmO', 'student');

-- Inserimento di edifici di esempio
INSERT INTO building (name, address) VALUES 
('Edificio A', 'Via Roma 10, Campus Centrale'),
('Edificio B', 'Viale delle Scienze 42, Polo Nord');

-- Inserimento di aule di esempio
INSERT INTO room (building_id, name) VALUES 
(1, 'Aula Magna'),
(1, 'Laboratorio Informatica 1'),
(1, 'Aula 101'),
(2, 'Aula 201'),
(2, 'Biblioteca');


-- Inserimento di issue
INSERT INTO issue (user_id, room_id, technician_id, title, description, status) VALUES
(3, 1, NULL, 'Proiettore non funzionante', 'Il proiettore principale non si accende, cavo di alimentazione apparentemente collegato.', 'open'),
(3, 1, 2, 'Sedie rotte', 'Tre sedie nella seconda fila hanno lo schienale rotto e sono pericolose.', 'in_progress'),
(3, 1, NULL, 'Finestra bloccata', 'La finestra in fondo a destra non si apre.', 'open'),
(3, 2, 2, 'Computer PC-05 non si avvia', 'Il computer PC-05 emette un beep lungo all''avvio ma lo schermo rimane nero.', 'in_progress'),
(3, 2, NULL, 'Manca un mouse', 'Alla postazione PC-12 manca il mouse.', 'open'),
(3, 3, NULL, 'Luce tremolante', 'La plafoniera centrale sfarfalla continuamente.', 'open');