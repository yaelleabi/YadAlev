# Installation

Une fois php, composer, mysql (ou postgresql) installés, il faut créer une base de données.

## Création de la base de données

Nous appelons cette base de données `yadalev` dans cet exemple.

Commande pour créer la base de données `yadalev`:

```sql
CREATE DATABASE yadalev;
```

Dans le fichier `.env` à la racine du projet, il faut modifier les lignes suivantes:

```bash
DATABASE_URL=mysql://user:password@host:port/yadalev
```

Par exemple, si vous avez un utilisateur `root` avec `root` en mot de passe, et que vous utilisez le port par défaut de mysql (3306), vous pouvez mettre:

```bash
DATABASE_URL=mysql://root:root@localhost:3306/yadalev
```

## Installation des dépendances

Pour installer les dépendances, il faut exécuter la commande suivante à la racine du projet:

```bash
composer install
```

## Création des tables

Pour créer les tables, il faut exécuter la commande suivante à la racine du projet:

```bash
php bin/console doctrine:migrations:migrate
```

Cette commande va créer les tables dans la base de données.

Les commandes SQL sont disponibles dans le fichier [`migrations/VersionInit.php`](migrations/VersionInit.php)

## Lancement du serveur

Pour lancer le serveur, il faut exécuter la commande suivante à la racine du projet:

```bash
symfony server:start
# Ou en utilisant --no-tls pour ne pas utiliser le protocole https (en développement)
symfony serve:start --no-tls
```

## Accès à l'interface d'administration

Pour accéder à l'interface d'administration, il faut créer un compte administrateur.
Pour cela, il faut exécuter la commande suivante à la racine du projet:

```bash
symfony console create:admin <email> <password>
# Exemple avec un email test@test.fr et un mot de passe Azerty123
symfony console create:admin test@test.fr Azerty123
```

Vous pouvez ensuite accéder à l'interface d'administration à l'adresse suivante: [http://localhost:8000/login](http://localhost:8000/login)
