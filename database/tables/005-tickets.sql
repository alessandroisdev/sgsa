CREATE TABLE tickets
(
    id             INT AUTO_INCREMENT PRIMARY KEY,
    number         INT                                                                NOT NULL, -- número sequencial diário
    date           DATE                                                               NOT NULL, -- data da emissão para reiniciar sequência
    ticket_type_id INT                                                                NOT NULL,
    sector_id      INT                                                                NOT NULL,
    status         ENUM ('waiting', 'called', 'attended', 'transferred', 'cancelled') NOT NULL DEFAULT 'waiting',
    created_at     DATETIME                                                                    DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME                                                                    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_type_id) REFERENCES ticket_types (id),
    FOREIGN KEY (sector_id) REFERENCES sectors (id),
    UNIQUE KEY unique_ticket_per_day (number, date, sector_id)
);