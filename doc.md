# 📘 Stack technique – Alydous'heure

## 🧩 Frontend

* **Framework** : [Vue.js 3](https://vuejs.org/)
* **Styling** : [Tailwind CSS](https://tailwindcss.com/)
* **Bundler/Dev Server** : [Vite](https://vitejs.dev/)

> * Architecture en composants
> * Design responsive-first, mobile-friendly
> * Vue Router pour la navigation
> * Appels API au backend Symfony via `fetch` ou `axios`

---

## 🔧 Backend

* **Framework PHP** : [Symfony 6/7](https://symfony.com/)
* **Authentification** : [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle) (JWT)
* **ORM** : [Doctrine](https://www.doctrine-project.org/)
* **Base de données** : [MySQL](https://www.mysql.com/)

### ✅ Fonctions backend prévues

* Authentification (JWT) pour la partie admin
* API REST (JSON) pour :

  * Prestations (services)
  * Prise de rendez-vous
  * Témoignages
  * Offres promotionnelles
  * Galerie (images/vidéos)
  * Newsletter (emails)
* Middleware de vérification des rôles (admin)

---

## 🗓️ Prise de rendez-vous

* **Librairie JS** : [FullCalendar.js](https://fullcalendar.io/)
* Vue dynamique d’un calendrier avec :

  * Création de créneaux
  * Enregistrement en base via API
  * Affichage des RDV existants
  * États possibles : libre / réservé / confirmé / annulé

---

## ☁️ Déploiement

### 🔹 Frontend (Vue)

* **Plateforme** : [Netlify](https://www.netlify.com/)

  * CI/CD via GitHub
  * HTTPS + nom de domaine custom possible
  * Formulaire de contact possible avec Netlify Forms ou API

### 🔹 Backend (Symfony)

* **Plateforme** : [Render.com](https://render.com/)

  * Service Web PHP
  * Connexion BDD MySQL hébergée (Render ou Railway)
  * Fichiers `.env` pour config de prod

> ⚠️ Prévois le déploiement du backend **avec build Symfony + composer install + migration BDD**

---

## 🧰 Outils utilisés

| Outil                       | Usage                                      |
| --------------------------- | ------------------------------------------ |
| **Thunder Client (VSCode)** | Tester les routes API REST depuis ton IDE  |
| **DBDiagram.io**            | Modéliser la base de données graphiquement |
| **Postman** (optionnel)     | Test avancé de l'API REST                  |
| **GitHub**                  | Versionnage + CI/CD Netlify/Render         |
| **Figma** (optionnel)       | Maquettage rapide de l’interface           |

---

## 🗂️ Schéma type de l’architecture

```
UTILISATEUR
   ↓ (navigateur)
[ Frontend Vue.js + Tailwind (Vite) ]
   ↓ (requêtes API REST)
[ Backend Symfony API + JWT ]
   ↓
[ MySQL ]
```
