CREATE TABLE users
(
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)                                   NOT NULL UNIQUE,
    password_hash VARCHAR(255)                                  NOT NULL,
    role          ENUM ('atendente', 'gestor', 'administrador') NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);