# Fonctionnalités

## 🔐 AUTHENTIFICATION & GESTION DES UTILISATEURS

### Utilisateurs (clients ou admin)

* [ ] 🔒 Inscription (client)
* [ ] 🔒 Connexion (JWT ou autre)
* [ ] 🔒 Déconnexion (côté front, mais refresh côté backend)
* [ ] 🔁 Refresh token (si besoin)
* [ ] 🔧 Modifier ses informations personnelles
* [ ] 🔧 Modifier son mot de passe
* [ ] 🔧 Réinitialisation de mot de passe (si tu veux le faire un jour)
* [ ] 🔍 Admin : liste des utilisateurs
* [ ] ❌ Admin : suppression de comptes

---

## 🛍️ PRODUITS

* [ ] 📄 Récupérer la liste des produits (filtrés par catégorie)
* [ ] 📄 Récupérer un produit par ID
* [ ] ✅ Ajouter un produit (admin)
* [ ] 🔧 Modifier un produit (admin)
* [ ] ❌ Supprimer un produit (admin)

---

## 🧴 PRESTATIONS

* [ ] 📄 Récupérer la liste des prestations (par catégorie)
* [ ] 📄 Récupérer une prestation par ID
* [ ] ✅ Ajouter une prestation (admin)
* [ ] 🔧 Modifier une prestation (admin)
* [ ] ❌ Supprimer une prestation (admin)

---

## 📅 DISPONIBILITÉS

* [ ] 📄 Récupérer les disponibilités de la semaine
* [ ] ✅ Ajouter une plage horaire disponible (admin)
* [ ] 🔧 Modifier une disponibilité (admin)
* [ ] ❌ Supprimer une disponibilité (admin)

Qui retourne les créneaux disponibles calculés dynamiquement.

---

## 📆 RÉSERVATIONS

* [ ] 📄 Lister ses propres réservations (client)
* [ ] 📄 Admin : lister toutes les réservations
* [ ] ✅ Prendre rendez-vous (client)
* [ ] 🔧 Modifier un rendez-vous (client ou admin)
* [ ] ❌ Annuler un rendez-vous (client ou admin)
* [ ] 🔁 Admin : changer le statut (confirmé / annulé)

---

## 🛒 ACHATS DE PRODUITS

* [ ] 📄 Lister ses achats (client)
* [ ] 📄 Admin : lister tous les achats
* [ ] ✅ Effectuer un achat (client) — sans paiement en ligne, juste une commande
* [ ] 🔁 Admin : changer le statut (livré / annulé)

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

## 📚 CATÉGORIES (Produits & Prestations)

* [ ] 📄 Lister toutes les catégories
* [ ] ✅ Ajouter une catégorie (admin)
* [ ] 🔧 Modifier une catégorie (admin)
* [ ] ❌ Supprimer une catégorie (admin)

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
