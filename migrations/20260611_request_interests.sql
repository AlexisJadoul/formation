-- À exécuter une seule fois sur une installation existante afin de permettre
-- aux visiteurs de signaler leur intérêt pour une demande validée.

CREATE TABLE request_interests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    participant_email VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_request_interest_email (request_id, participant_email),
    FOREIGN KEY (request_id) REFERENCES training_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
