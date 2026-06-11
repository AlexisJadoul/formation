-- À exécuter une seule fois sur une installation existante avant de publier
-- le nouveau parcours d'inscription publique.

ALTER TABLE slot_registrations
    ADD COLUMN participant_name VARCHAR(150) NULL AFTER user_id,
    ADD COLUMN participant_email VARCHAR(190) NULL AFTER participant_name;

UPDATE slot_registrations sr
JOIN users u ON u.id = sr.user_id
SET sr.participant_name = u.name,
    sr.participant_email = LOWER(u.email)
WHERE sr.participant_name IS NULL OR sr.participant_email IS NULL;

ALTER TABLE slot_registrations
    DROP FOREIGN KEY slot_registrations_ibfk_2,
    DROP INDEX uniq_registration,
    DROP COLUMN user_id,
    MODIFY participant_name VARCHAR(150) NOT NULL,
    MODIFY participant_email VARCHAR(190) NOT NULL,
    ADD UNIQUE KEY uniq_registration_email (slot_id, participant_email);
