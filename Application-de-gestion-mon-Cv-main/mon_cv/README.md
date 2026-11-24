# MON CV – Application Flutter

## 1. Présentation du projet

Mon CV est une application mobile et web développée avec Flutter et Dart dans le cadre des travaux pratiques TP1 et TP2.

L'application permet de présenter un curriculum vitae personnel, de consulter les coordonnées et de gérer les membres de la famille à travers une API REST développée en PHP et une base de données MySQL.

## 2. Technologies utilisées

- Flutter : développement de l'interface.
- Dart : langage de programmation.
- PHP : développement de l'API REST.
- MySQL : stockage des données.
- XAMPP : serveur local Apache et MySQL.
- phpMyAdmin : administration de la base de données.

## 3. Fonctionnalités

### Partie TP1 : Mon CV

- Présentation personnelle.
- Affichage de la formation.
- Présentation des compétences.
- Présentation des expériences.
- Affichage des coordonnées.

### Partie TP2 : Gestion familiale

- Création d'un compte utilisateur.
- Connexion avec vérification des identifiants par l'API.
- Affichage des membres de la famille depuis MySQL.
- Ajout d'un membre.
- Modification des informations d'un membre.
- Suppression d'un membre.
- Actualisation de la liste.

## 4. Installation et configuration

### Prérequis

- Flutter installé.
- Dart installé avec Flutter.
- XAMPP installé.
- Visual Studio Code.
- Google Chrome.

### Configuration de l'API

1. Télécharger et extraire le projet API PHP.
2. Placer le dossier de l'API dans le répertoire `htdocs` de XAMPP.
3. Démarrer Apache et MySQL.
4. Ouvrir phpMyAdmin.
5. Créer une base de données nommée `api`.
6. Importer le fichier `api.sql`.
7. Configurer le fichier `.env` avec les paramètres de connexion MySQL.

Configuration locale utilisée :

- Hôte : `127.0.0.1`
- Port : `3306`
- Base de données : `api`
- Utilisateur : `root`
- Mot de passe : vide (configuration locale XAMPP utilisée).

### Adresse de l'API

Adresse utilisée dans l'application Flutter :

`http://localhost/api-test-main/api-test-main/public`

Si le dossier de l'API change de nom ou d'emplacement, il faut adapter cette adresse dans `lib/services/api_service.dart`.

## 5. Lancement de l'application

Ouvrir le terminal dans le dossier du projet et exécuter :

```bash
flutter pub get
flutter run -d chrome
```

Pour utiliser l'application, Apache et MySQL doivent être démarrés dans XAMPP.

## 6. Organisation du projet Flutter

```text
mon_cv/
├── lib/
│   ├── main.dart
│   ├── login_page.dart
│   ├── famille_form.dart
│   └── services/
│       └── api_service.dart
├── test/
├── pubspec.yaml
└── README.md
```

## 7. API REST utilisée

Les principales routes utilisées sont :

| Méthode | Route | Fonction |
|---|---|---|
| POST | /accounts/register | Inscription |
| POST | /accounts/login | Connexion |
| GET | /famille-membres | Liste des membres |
| POST | /famille-membres | Ajouter un membre |
| PUT | /famille-membres/{id} | Modifier un membre |
| DELETE | /famille-membres/{id} | Supprimer un membre |

Les opérations de gestion familiale sont réalisées à travers l'API et les données sont stockées dans MySQL.

**Note de sécurité :** l'API utilisée vérifie les identifiants lors de la connexion, mais ses routes de gestion familiale ne sont pas protégées par une authentification côté serveur.

## 8. Démonstration vidéo

Lien de la vidéo de démonstration :

**À compléter après publication de la vidéo :**

[Ajouter ici le lien de la vidéo]

## 9. Auteur

Nom : KASOKI MUSAFIRI LYDIA  
Formation : Étudiant en informatique  
Projet : TP1 et TP2 – Développement Flutter avec API REST et MySQL  
Localisation : Goma, RDC

---

Projet réalisé dans le cadre de l'apprentissage du développement d'applications avec Flutter, PHP et MySQL.
