CREATE TABLE appointments
(
    id               INT AUTO_INCREMENT PRIMARY KEY,
    full_name        VARCHAR(150) NOT NULL,
    contact_info     VARCHAR(255) NOT NULL,
    document_info    VARCHAR(255) NOT NULL,
    sector_id        INT          NOT NULL,
    appointment_date DATE         NOT NULL,
    ticket_id        INT, -- senha gerada para o dia do agendamento
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sector_id) REFERENCES sectors (id),
    FOREIGN KEY (ticket_id) REFERENCES tickets (id)
);