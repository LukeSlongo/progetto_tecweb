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
    floor INT NOT NULL,
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
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL DEFAULT NULL,
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
('tecnico', '$2y$10$7nnn3YJsSpqn1O7VnWO50.n/CRCHlMagJEyhQ5TQe9m3J5vh3ihAO', 'technician');
('tecnico', '$2y$10$ekMDIhCRyKCf12oC/GC/led2NqD.1chaOwgO7x3witrVpKHK5xKwS', 'technician');

-- Inserimento di edifici di esempio
INSERT INTO building (name, address) VALUES 
('Edificio A', 'Via Roma 10, Campus Centrale'),
('Edificio B', 'Viale delle Scienze 42, Polo Nord');

-- Inserimento di aule di esempio
INSERT INTO room (building_id, name, floor) VALUES 
(1, 'Aula Magna', 0),
(1, 'Laboratorio Informatica 1', 1),
(1, 'Aula 101', 1),
(2, 'Aula 201', 2),
(2, 'Biblioteca', 0);