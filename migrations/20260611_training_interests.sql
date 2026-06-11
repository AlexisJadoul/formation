-- À exécuter une seule fois sur une installation existante afin de permettre
-- aux visiteurs de signaler leur intérêt pour un créneau sans s'y inscrire.

CREATE TABLE slot_interests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    participant_email VARCHAR(190) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_interest_email (slot_id, participant_email),
    FOREIGN KEY (slot_id) REFERENCES training_slots(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
