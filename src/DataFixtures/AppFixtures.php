<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Categorie;
use App\Entity\Prestation;
use App\Entity\Produit;
use App\Enum\CategorieType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Catégories
        $massageCat = new Categorie();
        $massageCat->setNom('Massages');
        $massageCat->setType(CategorieType::PRESTATION);
        $manager->persist($massageCat);

        $produitCat = new Categorie();
        $produitCat->setNom('Huiles');
        $produitCat->setType(CategorieType::PRODUIT);
        $manager->persist($produitCat);

        // Prestations
        $prestation = new Prestation();
        $prestation->setTitre('Massage relaxant');
        $prestation->setDescription('Massage du corps complet pour la détente.');
        $prestation->setPrix(60.00);
        $prestation->setDuree(60);
        $prestation->setCategorie($massageCat);
        $manager->persist($prestation);

        // Produits
        $produit = new Produit();
        $produit->setTitre('Huile essentielle lavande');
        $produit->setDescription('Huile apaisante pour le massage.');
        $produit->setPrix(15.00);
        $produit->setStock(20);
        $produit->setCategorie($produitCat);
        $manager->persist($produit);

        // Utilisateur admin
        $admin = new User();
        $admin->setEmail('admin@massage.com');
        $admin->setNom('Alice');
        $admin->setPrenom('Admin');
        $admin->setTelephone('0696123456');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->hasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);
        $manager->persist($admin);

        // Utilisateur client
        $client = new User();
        $client->setEmail('client@massage.com');
        $client->setNom('Chloe');
        $client->setPrenom('Client');
        $client->setTelephone('0696987654');
        $client->setRoles(['ROLE_CLIENT']);
        $hashedPassword = $this->hasher->hashPassword($client, 'client123');
        $client->setPassword($hashedPassword);
        $manager->persist($client);

        $manager->flush();
    }
}

