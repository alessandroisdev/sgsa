CREATE TABLE transfers
(
    id             INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id      INT NOT NULL,
    from_sector_id INT NOT NULL,
    to_sector_id   INT NOT NULL,
    user_id        INT NOT NULL,
    timestamp      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE,
    FOREIGN KEY (from_sector_id) REFERENCES sectors (id),
    FOREIGN KEY (to_sector_id) REFERENCES sectors (id),
    FOREIGN KEY (user_id) REFERENCES users (id)
);