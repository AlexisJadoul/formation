# Plateforme de créneaux de formation

Site PHP/MySQL simple proposé en consultation publique pour :
- afficher les demandes de formation validées et leur nombre de votes ;
- présenter les prochains créneaux de formation et leur taux de remplissage ;
- consulter le détail de chaque créneau sans compte ni connexion ;
- inscrire un participant à un créneau avec son nom et son adresse email, sans connexion.

Les parcours de création de compte, de connexion, de création de demande et de vote sont désactivés. L’inscription publique aux créneaux reste disponible. Les écrans de gestion restent protégés et ne sont pas exposés dans la navigation publique.

## Installation rapide

1. Envoyer tous les fichiers sur l'hébergement.
2. Créer une base de données MySQL.
3. Modifier `config.php` avec les accès MySQL.
4. Ouvrir `install.php` dans le navigateur.
5. Créer le compte administrateur depuis l'écran d'installation.
6. Supprimer `install.php` après installation.

Pour mettre à jour une installation existante, exécuter une seule fois `migrations/20260611_public_registrations.sql` sur la base de données avant de publier les nouveaux fichiers.

## Accès public en consultation

Une fois installé, `index.php` ouvre directement le tableau de bord public. Les visiteurs peuvent naviguer sur l’accueil, consulter les demandes validées, voir les créneaux, ouvrir leur détail et inscrire un participant sans créer de compte ni se connecter. Le formulaire accepte le nom et l’adresse email de la personne concernée, ce qui permet également d’inscrire quelqu’un d’autre.

La connexion, la création de compte, la création de demande et le vote sont désactivés. Les pages de gestion restent protégées afin de ne pas exposer les données privées ni les actions administratives.
