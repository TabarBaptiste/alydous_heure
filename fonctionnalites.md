# Fonctionnalités

## 🔐 AUTHENTIFICATION & GESTION DES UTILISATEURS

### Utilisateurs (clients ou admin)

* [ ] 🔒 Inscription (client)
* [x] 🔒 Connexion (JWT ou autre)
* [ ] 🔒 Déconnexion (côté front, mais refresh côté backend)
* [ ] 🔁 Refresh token (si besoin)
* [ ] 🔧 Modifier ses informations personnelles
* [ ] 🔧 Modifier son mot de passe
* [ ] 🔧 Réinitialisation de mot de passe (si tu veux le faire un jour)
* [ ] 🔍 Admin : liste des utilisateurs
* [ ] ❌ Admin : suppression de comptes

---

## 🛍️ PRODUITS

* [x] 📄 Récupérer la liste des produits (filtrés par catégorie)
* [x] 📄 Récupérer un produit par ID
* [x] ✅ Ajouter un produit (admin)
* [x] 🔧 Modifier un produit (admin)
* [x] ❌ Supprimer un produit (admin)

---

## 🧴 PRESTATIONS

* [x] 📄 Récupérer la liste des prestations (par catégorie)
* [x] 📄 Récupérer une prestation par ID
* [x] ✅ Ajouter une prestation (admin)
* [x] 🔧 Modifier une prestation (admin)
* [x] ❌ Supprimer une prestation (admin)

---

## 📅 DISPONIBILITÉS

* [x] 📄 Récupérer les disponibilités de la semaine
* [x] ✅ Ajouter une plage horaire disponible (admin)
* [x] 🔧 Modifier une disponibilité (admin)
* [x] ❌ Supprimer une disponibilité (admin)

Qui retourne les créneaux disponibles calculés dynamiquement.

---

## 📆 RÉSERVATIONS

* [x] ✅ Prendre rendez-vous (client)
* [x] 📄 Lister ses propres réservations (client)
* [x] 📄 Admin : lister toutes les réservations
* [x] 🔧 Modifier un rendez-vous (client ou admin)
* [x] ❌ Annuler un rendez-vous (client ou admin)
* [x] 🔁 Admin : changer le statut (confirmé / annulé)

---

## 🛒 ACHATS DE PRODUITS

* [x] 📄 Lister ses achats (client)
* [x] 📄 Admin : lister tous les achats
* [x] ✅ Effectuer un achat (client) — sans paiement en ligne, juste une commande
* [x] 🔁 Admin : changer le statut (livré / annulé)

---

## 📚 CATÉGORIES (Produits & Prestations)

* [x] 📄 Lister toutes les catégories
* [x] ✅ Ajouter une catégorie (admin)
* [x] 🔧 Modifier une catégorie (admin)
* [x] ❌ Supprimer une catégorie (admin)

---

## 💬 TÉMOIGNAGES / AVIS

* [ ] 📄 Lister les témoignages (public)
* [ ] ✅ Ajouter un témoignage (client)
* [ ] ❌ Supprimer/modérer un témoignage (admin)

---

## 🖼️ GALERIE (Photos & Vidéos)

* [ ] 📄 Lister les médias (public)
* [ ] ✅ Ajouter un média (admin)
* [ ] ❌ Supprimer un média (admin)

---

## 📨 NEWSLETTER (optionnel)

* [ ] ✅ S’inscrire à la newsletter
* [ ] ❌ Se désinscrire
* [ ] 📤 Admin : envoyer une newsletter (si fait depuis back)

---

## 🛠️ ADMIN DASHBOARD (API utilisée par le front admin)

* [ ] 📊 Statistiques simples : nb de réservations, nb d’achats, chiffre d’affaires estimé, créneaux disponibles
* [ ] 🔍 Rechercher par nom/email/date dans les réservations ou achats

---

<!-- ## ✅ Résumé des entités concernées

| Entité        | CRUD Public | CRUD Admin | API spéciale         |
| ------------- | ----------- | ---------- | -------------------- |
| User          | Oui         | Oui        | Auth, rôles, profil  |
| Prestation    | Non         | Oui        | Liste, par catégorie |
| Produit       | Non         | Oui        | Achats               |
| Categorie     | Oui         | Oui        | -                    |
| Disponibilité | Non         | Oui        | Calcul calendrier    |
| Réservation   | Oui         | Oui        | Gestion planning     |
| Achat         | Oui         | Oui        | -                    |
| Témoignage    | Oui         | Oui        | -                    |
| Galerie       | Oui         | Oui        | -                    | -->
