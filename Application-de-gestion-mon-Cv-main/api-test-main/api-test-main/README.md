# API Test

API REST en PHP (sans framework) pour gérer des étudiants, des membres de famille et des comptes utilisateurs. Toutes les réponses sont au format JSON.

## Installation

1. Placer le projet dans `C:\xampp\htdocs\api-test` et démarrer Apache et MySQL depuis XAMPP.
2. Créer une base de données `api` vide, puis y importer `api.sql` (phpMyAdmin > Importer, ou `mysql -u root api < api.sql`). Ce fichier crée les trois tables : `tbl_etudiant`, `famille_membre` et `account`.
3. Copier `.env.example` en `.env` et adapter les identifiants si besoin :

   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

## URL de base

```
http://localhost/api-test/public
```

Le segment `/public` est obligatoire : sans lui, toutes les routes renvoient `404 Route introuvable`.

Aucun endpoint ne demande d'authentification : chaque endpoint peut être appelé seul.

## Format des réponses

Succès :

```json
{ "success": true, "message": "...", "data": { } }
```

Erreur :

```json
{ "success": false, "message": "Données invalides", "errors": { "nom": "Le champ nom est obligatoire" } }
```

| Code | Signification |
|------|---------------|
| 200 | Requête réussie |
| 201 | Ressource créée |
| 401 | Email ou mot de passe incorrect |
| 404 | Ressource ou route introuvable |
| 405 | Méthode non autorisée sur cette route |
| 409 | Email déjà utilisé |
| 422 | Données invalides (détails dans `errors`) |
| 500 | Erreur de connexion à la base de données |

Pour les requêtes avec un body JSON, envoyer l'en-tête `Content-Type: application/json`.

---

## Comptes

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/accounts/register` | Créer un compte |
| POST | `/accounts/login` | Se connecter |

### Créer un compte

`POST /accounts/register`

```json
{
  "email": "test@exemple.com",
  "password": "secret123"
}
```

- `email` : obligatoire, adresse valide, 100 caractères maximum, unique.
- `password` : obligatoire, 6 caractères minimum. Il est stocké chiffré (hash).

Réponse `201` :

```json
{
  "success": true,
  "message": "Compte créé",
  "data": { "id": 1, "email": "test@exemple.com", "created_at": "2026-11-20 20:00:31" }
}
```

Erreurs : `409` si l'email existe déjà, `422` si les données sont invalides.

### Se connecter

`POST /accounts/login`

```json
{
  "email": "test@exemple.com",
  "password": "secret123"
}
```

Réponse `200` :

```json
{
  "success": true,
  "message": "Connexion réussie",
  "data": { "id": 1, "email": "test@exemple.com", "created_at": "2026-11-20 20:00:31" }
}
```

Erreur : `401` si l'email ou le mot de passe est incorrect. Le mot de passe n'est jamais renvoyé.

---

## Membres de famille

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/famille-membres` | Lister les membres |
| GET | `/famille-membres/{id}` | Afficher un membre |
| POST | `/famille-membres` | Créer un membre (form-data avec image, ou JSON) |
| PUT | `/famille-membres/{id}` | Modifier un membre (JSON, l'image est conservée) |
| POST | `/famille-membres/{id}` | Modifier un membre et changer son image (form-data) |
| DELETE | `/famille-membres/{id}` | Supprimer un membre et son image |

### Champs

| Champ | Type | Règles |
|-------|------|--------|
| `nom` | texte | obligatoire, 50 caractères max |
| `postnom` | texte | obligatoire, 50 caractères max |
| `prenom` | texte | obligatoire, 50 caractères max |
| `lieu_naissance` | texte | obligatoire, 100 caractères max |
| `date_naissance` | date | obligatoire, format `AAAA-MM-JJ`, pas dans le futur |
| `parente` | texte | obligatoire, 50 caractères max (ex. : Père, Mère, Frère) |
| `image` | fichier | facultatif, jpg / png / gif / webp, 2 Mo max |

Pour une modification, tous les champs obligatoires doivent être renvoyés, pas seulement ceux qui changent.

### Créer un membre avec une image

`POST /famille-membres` en **multipart/form-data** :

| Clé | Valeur |
|-----|--------|
| `nom` | Baraka |
| `postnom` | Bigega |
| `prenom` | Papa |
| `lieu_naissance` | Goma |
| `date_naissance` | 1970-05-12 |
| `parente` | Père |
| `image` | *(fichier)* |

Dans Postman : Body → form-data, et passer le type de la clé `image` de Text à File.

Exemple avec curl :

```bash
curl -X POST http://localhost/api-test/public/famille-membres \
  -F nom=Baraka -F postnom=Bigega -F prenom=Papa \
  -F lieu_naissance=Goma -F date_naissance=1970-05-12 -F parente=Père \
  -F image=@photo.jpg
```

### Créer un membre sans image

`POST /famille-membres` en JSON :

```json
{
  "nom": "Baraka",
  "postnom": "Bigega",
  "prenom": "Maman",
  "lieu_naissance": "Bukavu",
  "date_naissance": "1975-02-14",
  "parente": "Mère"
}
```

Réponse `201` :

```json
{
  "success": true,
  "message": "Membre créé",
  "data": {
    "id": 1,
    "nom": "Baraka",
    "postnom": "Bigega",
    "prenom": "Papa",
    "lieu_naissance": "Goma",
    "date_naissance": "1970-05-12",
    "image": "uploads/famille/3f0d9bd9fc64ca7cd891541c4c009c2b.png",
    "parente": "Père",
    "image_url": "http://localhost/api-test/public/uploads/famille/3f0d9bd9fc64ca7cd891541c4c009c2b.png"
  }
}
```

`image_url` est l'adresse complète de l'image, à utiliser directement dans une balise `<img>`. Elle vaut `null` si le membre n'a pas d'image.

### Modifier un membre

- **Sans changer l'image :** `PUT /famille-membres/{id}` avec le même body JSON que pour la création. L'image actuelle est conservée.
- **En changeant l'image :** `POST /famille-membres/{id}` en form-data, comme pour la création. L'ancienne image est supprimée du serveur.

Le `PUT` ne peut pas recevoir de fichier, car PHP ne lit pas les fichiers envoyés avec cette méthode : il faut donc utiliser `POST` pour changer l'image.

### Supprimer un membre

`DELETE /famille-membres/{id}` supprime le membre et son fichier image.

```json
{ "success": true, "message": "Membre supprimé" }
```

---

## Étudiants

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/etudiants` | Lister les étudiants |
| GET | `/etudiants/{id}` | Afficher un étudiant |
| POST | `/etudiants` | Créer un étudiant |
| PUT | `/etudiants/{id}` | Modifier un étudiant |
| DELETE | `/etudiants/{id}` | Supprimer un étudiant |

Body JSON pour `POST` et `PUT` (tous les champs sont obligatoires) :

```json
{
  "nom": "Kabila",
  "postnom": "Mwamba",
  "prenom": "Jean",
  "adresse": "Kinshasa",
  "telephone": "+243900000000"
}
```

| Champ | Longueur max |
|-------|--------------|
| `nom`, `postnom`, `prenom` | 50 |
| `adresse` | 100 |
| `telephone` | 15 |

---

## Structure du projet

```
api-test/
├── public/
│   ├── index.php              # Point d'entrée et routes
│   └── uploads/famille/       # Images des membres (créé automatiquement)
├── src/
│   ├── Database.php           # Connexion PDO
│   ├── Controllers/           # Un contrôleur par ressource
│   └── Models/                # Un modèle par table
├── config/database.php
└── api.sql                    # Toutes les tables (à importer une seule fois)
```
