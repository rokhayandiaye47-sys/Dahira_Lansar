# Dahira Lansar Guidick

Application web de gestion des membres et des opérations associatives de la Dahira Lansar Guidick.

## Description

Ce projet est une plateforme PHP/MySQL destinée à gérer :

- les demandes d'adhésion,
- les membres et leurs générations,
- les cotisations,
- la caisse et les dépenses,
- les paiements,
- les campagnes,
- les comptes à responsabilité,
- les notifications internes,
- l'authentification et la gestion des rôles.

L'application expose un menu dynamique selon le rôle connecté : administrateur, responsable de génération, gestionnaire des membres, trésorier ou membre simple.

## Stack technique

- PHP
- MySQL / MariaDB
- PDO pour les accès à la base de données
- HTML/CSS/JS vanilla
- WAMP / XAMPP / environnement Apache local

## Prérequis

- Apache avec PHP installé
- MySQL ou MariaDB
- Un serveur local type WAMP/XAMPP
- Accès au dossier web du serveur (par exemple `www` pour WAMP)

## Installation

1. Cloner le dépôt dans le dossier web de votre serveur local :

   ```bash
   git clone https://github.com/rokhayandiaye47-sys/Dahira_Lansar.git
   ```

2. Déplacer le projet dans le dossier accessible par votre serveur, par exemple :

   ```text
   C:\wamp64\www\dahira
   ```

3. Créer la base de données MySQL.

4. Importer les scripts SQL disponibles dans le dossier `sql/` si nécessaire.

5. Configurer la connexion à la base de données dans `config/database.php` ou via variables d'environnement.

## Configuration de la base

Le projet lit les paramètres suivants :

- `DAHIRA_DB_HOST` (par défaut : `localhost`)
- `DAHIRA_DB_NAME` (par défaut : `dahira_lansar_guidick`)
- `DAHIRA_DB_USER` (par défaut : `root`)
- `DAHIRA_DB_PASSWORD`
- `DAHIRA_DEBUG` pour activer le mode debug

Exemple de variables d'environnement sous Windows PowerShell :

```powershell
$env:DAHIRA_DB_HOST = "localhost"
$env:DAHIRA_DB_NAME = "dahira_lansar_guidick"
$env:DAHIRA_DB_USER = "root"
$env:DAHIRA_DB_PASSWORD = ""
```

La configuration par défaut est visible dans `config/database.php`.

## Lancement

Démarrer Apache et MySQL, puis ouvrir dans le navigateur :

```text
http://localhost/dahira/
```

Si l'utilisateur n'est pas connecté, l'application redirige vers la page d'accueil puis vers le module d'authentification.

## Structure du projet

```text
.
├── accueil.php
├── index.php
├── auth/
├── caisse/
├── campagne/
├── config/
├── cotisations/
├── demandes/
├── depenses/
├── generations/
├── includes/
├── image/
├── membres/
├── notifications/
├── paiements/
├── phpmailer/
├── sql/
├── statistiques/
├── utilisateurs/
├── .gitignore
├── README.md
└── ...
```

## Rôles principaux

Le système prend en charge plusieurs types de profils :

- Administrateur général
- Responsable de génération
- Gestionnaire des membres
- Trésorier
- Membre simple

## Sécurité

- Ne pas utiliser le compte `root` MySQL en production.
- Utiliser un utilisateur dédié avec les droits minimaux requis.
- Garder `DAHIRA_DEBUG` désactivé en environnement de production.
- Vérifier les permissions et les contrôles d'accès avant toute mise en production.

## Développement

Le projet est organisé sous forme de modules séparés par fonctionnalité. Chaque dossier correspond généralement à une zone fonctionnelle : `membres`, `cotisations`, `caisse`, `paiements`, `utilisateurs`, etc.

## Auteurs / projet

Projet développé pour la Dahira Lansar Guidick.
