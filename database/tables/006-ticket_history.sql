CREATE TABLE ticket_history
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id   INT         NOT NULL,
    action      VARCHAR(50) NOT NULL, -- ex: 'called', 'transferred', 'attended'
    description TEXT,
    user_id     INT         NOT NULL,
    timestamp   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets (id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users (id)
);