# Plateforme de créneaux de formation

Site PHP/MySQL simple pour :
- permettre aux agents de créer des demandes de formation ;
- permettre aux admins de valider ou refuser ces demandes ;
- rendre les demandes validées visibles aux autres agents ;
- permettre aux agents de voter pour les demandes qui les intéressent ;
- permettre aux admins de créer, modifier et supprimer des créneaux de formation ;
- permettre aux agents de s'inscrire à un créneau existant.

## Installation rapide

1. Envoyer tous les fichiers sur l'hébergement.
2. Créer une base de données MySQL.
3. Modifier `config.php` avec les accès MySQL.
4. Ouvrir `install.php` dans le navigateur.
5. Créer le compte administrateur depuis l'écran d'installation.
6. Supprimer `install.php` après installation.

## Rôles

### Admin
- voit toutes les demandes ;
- valide ou refuse les demandes ;
- crée et modifie les créneaux de formation ;
- voit les inscriptions.

### Agent
- crée une demande de formation ;
- vote pour une demande validée ;
- consulte les créneaux ;
- s'inscrit à un créneau.

## Accès

Une fois installé :
- `login.php` : connexion ;
- `register.php` : création de compte agent ;
- `dashboard.php` : tableau de bord.
