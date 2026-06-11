-- À exécuter une seule fois après 20260611_public_registrations.sql sur une
-- installation existante. Une inscription à un créneau ne nécessite plus que
-- l'adresse e-mail du participant.

ALTER TABLE slot_registrations
    DROP COLUMN participant_name;
