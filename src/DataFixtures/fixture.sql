-- ===========================
-- 🚀 FIXTURES POUR RAILWAY
-- ===========================

-- Catégories
INSERT INTO `categorie` (`id`, `nom`, `type`) VALUES
(5, 'Massages', 'prestation'),
(6, 'Huiles', 'produit');

-- Prestations
INSERT INTO `prestation` (`id`, `categorie_id`, `titre`, `description`, `prix`, `duree`) VALUES
(3, 5, 'Massage relaxant', 'Massage du corps complet pour la détente.', 65, 60),
(4, 5, 'Massage tonique', 'Le massage tonique prépare et apaise les muscles avant et après l\'effort...', 80, 45);

-- Produits
INSERT INTO `produit` (`id`, `categorie_id`, `titre`, `description`, `prix`, `stock`) VALUES
(3, 6, 'Huile essentielle lavande', 'Huile apaisante pour le massage.', 15, 15),
(4, 5, 'test produit back', 'Pas de description, back', 90, 150);

-- Utilisateurs
INSERT INTO `user` (`id`, `email`, `password`, `nom`, `prenom`, `telephone`, `created_at`, `roles`) VALUES
(3, 'admin@massage.com', '$2y$13$s5FZ2sLO9m4LPEDNvgCG4erBJdDYLhVzhGrva5u4lTRWb3yPON8m.', 'Alice', 'Admin', 696123456, '2025-06-08 13:54:41', '["ROLE_ADMIN"]'),
(4, 'client@massage.com', '$2y$13$nEVWiJDegX2dpyjjW6WxZeJl8xpKQNxqdDWkF7lTeEjKa4qYDMo02', 'Chloe', 'Client', 696987654, '2025-06-08 13:54:41', '["ROLE_CLIENT"]');

-- Disponibilités
INSERT INTO `disponibilite` (`id`, `jour`, `start_time`, `end_time`, `is_disponible`) VALUES
(1, 'Lundi', '09:00:00', '12:00:00', 1),
(2, 'Lundi', '14:00:00', '17:00:00', 1),
(3, 'Mardi', '09:00:00', '12:00:00', 1),
(4, 'Mardi', '14:00:00', '17:00:00', 1),
(5, 'Mercredi', '09:00:00', '12:00:00', 1),
(6, 'Mercredi', '14:00:00', '17:00:00', 1),
(7, 'Jeudi', '09:00:00', '12:00:00', 1),
(8, 'Jeudi', '14:00:00', '17:00:00', 1),
(9, 'Vendredi', '09:00:00', '12:00:00', 1),
(10, 'Vendredi', '14:00:00', '17:00:00', 1),
(11, 'Samedi', '09:00:00', '12:00:00', 1),
(12, 'Samedi', '14:00:00', '17:00:00', 1);

-- Achats
INSERT INTO `achat` (`id`, `user_id`, `produit_id`, `quantite`, `date_achat`, `statut`) VALUES
(2, 3, 3, 5, '2025-06-15 17:06:53', 'en_attente'),
(3, 3, 3, 2, '2025-06-15 17:09:20', 'en_attente');

-- Réservations
INSERT INTO `reservation` (`id`, `user_id`, `prestation_id`, `date`, `start_time`, `end_time`, `statut`, `created_at`) VALUES
(1, 3, 4, '2025-06-30', '09:00:00', '09:45:00', 'confirmee', '2025-06-28 22:17:29');

-- Migrations (optionnel, sauf si tu veux les traquer)
INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250608130848', '2025-06-08 13:09:02', 478),
('DoctrineMigrations\\Version20250608131730', '2025-06-08 13:17:36', 31),
('DoctrineMigrations\\Version20250608134849', '2025-06-08 13:48:55', 18),
('DoctrineMigrations\\Version20250615083501', '2025-06-15 08:35:14', 394),
('DoctrineMigrations\\Version20250615141534', '2025-06-15 14:15:42', 104);
