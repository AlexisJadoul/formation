# Plateforme de créneaux de formation

Site PHP/MySQL simple proposé en consultation publique pour :
- afficher les demandes de formation validées et leur nombre de votes ;
- présenter les prochains créneaux de formation et leur taux de remplissage ;
- consulter le détail de chaque créneau sans compte ni connexion.

Les anciens parcours d’écriture (création de compte, connexion, création de demande, vote et inscription à un créneau) sont désactivés. Les écrans de gestion restent protégés et ne sont pas exposés dans la navigation publique.

## Installation rapide

1. Envoyer tous les fichiers sur l'hébergement.
2. Créer une base de données MySQL.
3. Modifier `config.php` avec les accès MySQL.
4. Ouvrir `install.php` dans le navigateur.
5. Créer le compte administrateur depuis l'écran d'installation.
6. Supprimer `install.php` après installation.

## Accès public en consultation

Une fois installé, `index.php` ouvre directement le tableau de bord public. Les visiteurs peuvent naviguer sur l’accueil, consulter les demandes validées, voir les créneaux et ouvrir leur détail sans créer de compte ni se connecter.

La connexion, la création de compte et toutes les actions d’écriture accessibles aux visiteurs (création de demande, vote et inscription à un créneau) sont désactivées. Les pages de gestion restent protégées afin de ne pas exposer les données privées ni les actions administratives.
