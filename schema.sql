-- Base MySQL de Mention
CREATE DATABASE IF NOT EXISTS mention CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mention;

CREATE TABLE IF NOT EXISTS analyses (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    client_hash CHAR(64) NOT NULL,          -- empreinte journalière de l'IP, pour la limitation de débit
    note        DECIMAL(4,1) NOT NULL,      -- note du CV d'origine (/20)
    note_apres  DECIMAL(4,1) NOT NULL,      -- note estimée après réécriture (/20)
    criteres    JSON NOT NULL,              -- {"Impact et résultats": 8, ...}
    with_offer  TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_client_time (client_hash, created_at)
) ENGINE=InnoDB;
