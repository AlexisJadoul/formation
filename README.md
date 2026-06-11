# Plateforme de créneaux de formation

Site PHP/MySQL simple proposé en consultation publique pour :
- afficher les demandes de formation validées et leur nombre de personnes intéressées ;
- présenter les prochains créneaux de formation et leur taux de remplissage ;
- consulter le détail de chaque créneau sans compte ni connexion ;
- permettre à chaque visiteur de créer une demande de formation avec sa seule adresse e-mail, sans connexion ;
- permettre à chaque visiteur de s’inscrire à un créneau disponible ou de signaler son intérêt lorsqu’il est complet ;
- permettre à chaque visiteur de signaler son intérêt pour une demande de formation validée ;
- permettre aux administrateurs de consulter les adresses e-mail des inscrits et des personnes intéressées pour chaque créneau.

La création publique de compte et le vote sont désactivés. La création de demandes, l’inscription aux créneaux et la déclaration d’intérêt restent publiques. Seuls les administrateurs disposent d’un compte et peuvent se connecter pour accéder aux écrans de gestion.

## Installation rapide

1. Envoyer tous les fichiers sur l'hébergement.
2. Créer une base de données MySQL.
3. Modifier `config.php` avec les accès MySQL.
4. Ouvrir `install.php` dans le navigateur.
5. Créer le compte administrateur depuis l'écran d'installation.
6. Supprimer `install.php` après installation.

Pour mettre à jour une installation existante, exécuter une seule fois `migrations/20260611_public_registrations.sql`, puis `migrations/20260611_public_training_requests.sql`, puis `migrations/20260611_email_only_registrations.sql`, puis `migrations/20260611_training_interests.sql`, puis `migrations/20260611_request_interests.sql`, sur la base de données avant de publier les nouveaux fichiers.

## Accès public

Une fois installé, `index.php` ouvre directement le tableau de bord public. Les visiteurs peuvent naviguer sur l’accueil, consulter les demandes validées, créer une demande, voir les créneaux, ouvrir leur détail et inscrire un participant sans créer de compte ni se connecter. La création d’une demande nécessite uniquement une adresse e-mail, qui identifie son auteur et reste visible seulement par les administrateurs. Les adresses e-mail demandées dans les parcours publics sont saisies dans une fenêtre dédiée. L’adresse e-mail identifie son inscription, empêche une double inscription au même créneau et n’est jamais affichée sur les pages publiques.

La création publique de compte et le vote sont désactivés. La connexion est réservée aux administrateurs, et les pages de gestion restent protégées afin de ne pas exposer les données privées ni les actions administratives.
