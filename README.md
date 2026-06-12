# 🚀 Cours API REST avec Symfony 7 + Docker

Support de cours **5 heures** pour étudiants **BAC+3**.
API REST écrite **à la main** (contrôleurs, serializer, validation), base **MariaDB/MySQL**, le tout **dockerisé**.

> Domaine métier : un petit **catalogue de produits** (catégories + produits), avec
> authentification par **token JWT**.

---

## 📦 Ce que contient le dépôt

| Fichier / dossier | Rôle |
|---|---|
| `COURS.md` | Le support de cours complet, découpé en 5 heures |
| `EXERCICES.md` | Les énoncés des exercices (1 par heure + bonus) |
| `CORRIGES.md` | Les corrigés détaillés |
| `docker-compose.yml` | Orchestration : PHP-FPM, Nginx, MariaDB, Adminer |
| `docker/` | Dockerfile PHP + config Nginx + init SQL |
| `Makefile` | Raccourcis (`make setup`, `make migrate`…) |
| `app/` | Le projet Symfony |

---

## ⚡ Démarrage rapide (TL;DR)

Pré-requis : **Docker Desktop** installé et démarré.

```bash
# 1. Tout installer et lancer d'un coup
make setup

# 2. C'est prêt :
#    API      → http://localhost:8080
#    Adminer  → http://localhost:8081   (serveur: database, user: app, mdp: app)
```

> Pas de `make` sous Windows ? Voir la section « Sans Make » plus bas.

### Tester en 30 secondes

```bash
# Lister les produits (public)
curl http://localhost:8080/api/products

# Se connecter (compte de démo créé par les fixtures)
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# → copier le "token" renvoyé, puis créer un produit :
curl -X POST http://localhost:8080/api/products \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <TON_TOKEN>" \
  -d '{"name":"Webcam 4K","priceCents":7990,"stock":12,"categoryId":2}'
```

---

## 🧭 Endpoints disponibles

| Méthode | URL | Auth | Description |
|---|---|---|---|
| `GET` | `/` | non | Page d'accueil (liste des routes) |
| `POST` | `/api/register` | non | Créer un compte |
| `POST` | `/api/login` | non | Obtenir un token JWT |
| `GET` | `/api/me` | **oui** | Profil de l'utilisateur connecté |
| `GET` | `/api/categories` | non | Lister les catégories |
| `GET` | `/api/categories/{id}` | non | Détail d'une catégorie |
| `POST` | `/api/categories` | **oui** | Créer une catégorie |
| `PUT/PATCH` | `/api/categories/{id}` | **oui** | Modifier une catégorie |
| `DELETE` | `/api/categories/{id}` | **oui** | Supprimer une catégorie |
| `GET` | `/api/products` | non | Lister les produits *(filtres : `q`, `category`, `page`, `limit`)* |
| `GET` | `/api/products/{id}` | non | Détail d'un produit |
| `POST` | `/api/products` | **oui** | Créer un produit |
| `PUT/PATCH` | `/api/products/{id}` | **oui** | Modifier un produit |
| `DELETE` | `/api/products/{id}` | **oui** | Supprimer un produit |

Compte de démonstration : **admin@example.com** / **password**

---

## 🛠️ Commandes utiles (`make help`)

```
make setup      # Installe TOUT (build + deps + base + migrations + fixtures + clés JWT)
make up         # Démarre les conteneurs
make down       # Arrête les conteneurs
make sh         # Shell dans le conteneur PHP
make migrate    # Joue les migrations
make fixtures   # Recharge les données de démo
make test       # Lance les tests PHPUnit
make logs       # Affiche les logs
```

---

## 🪟 Sans Make (Windows / PowerShell)

```bash
docker compose build
docker compose up -d
docker compose exec php composer install
docker compose exec php php bin/console lexik:jwt:generate-keypair --skip-if-exists
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

---

## 🧩 Stack technique

- **PHP 8.3** (FPM) + **Symfony 7.3**
- **MariaDB 11.4** (compatible MySQL) via **Doctrine ORM 3**
- **Nginx** comme serveur web
- **LexikJWTAuthenticationBundle** pour l'authentification par token
- **Adminer** pour explorer la base graphiquement
- **PHPUnit 11** pour les tests fonctionnels

---

## 🆘 Dépannage

| Symptôme | Solution |
|---|---|
| `port is already allocated` | Un service occupe déjà 8080/3306. Changez le port dans `docker-compose.yml`. |
| `JWT Token not found` | En-tête manquant : `Authorization: Bearer <token>`. |
| `Access denied ... app_test` | Recréez le volume : `docker compose down -v && make setup`. |
| Modif d'entité non prise en compte | `make diff` puis `make migrate`. |
| Comportement bizarre | `make cc` (vide le cache Symfony). |
