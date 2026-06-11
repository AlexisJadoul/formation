-- À exécuter une seule fois sur une installation existante avant de publier
-- le parcours de création publique des demandes de formation.

ALTER TABLE training_requests
    ADD COLUMN requester_email VARCHAR(190) NULL AFTER user_id,
    MODIFY user_id INT NULL;

UPDATE training_requests tr
JOIN users u ON u.id = tr.user_id
SET tr.requester_email = LOWER(u.email)
WHERE tr.requester_email IS NULL;

ALTER TABLE training_requests
    MODIFY requester_email VARCHAR(190) NOT NULL;
