CREATE TABLE ticket_types
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL UNIQUE,
    priority   INT          NOT NULL DEFAULT 0, -- menor número = maior prioridade
    created_at DATETIME              DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME              DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);