<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/produit')]
final class ProduitController extends AbstractController
{
    #[Route('', name: 'api_produits', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $categorieId = $request->query->get('categorie');

        if ($categorieId) {
            $produits = $produitRepository->findBy(['categorie' => $categorieId]);
        } else {
            $produits = $produitRepository->findAll();
        }

        $data = array_map(fn(Produit $p) => [
            'id' => $p->getId(),
            'titre' => $p->getTitre(),
            'description' => $p->getDescription(),
            'prix' => $p->getPrix(),
            'stock' => $p->getStock(),
            'categorie' => $p->getCategorie()?->getNom(),
        ], $produits);

        return new JsonResponse($data, 200);
    }

    #[Route('/{id}', name: 'api_produits_show', methods: ['GET'])]
    public function show(Produit $produit): JsonResponse
    {
        $data = [
            'id' => $produit->getId(),
            'titre' => $produit->getTitre(),
            'description' => $produit->getDescription(),
            'prix' => $produit->getPrix(),
            'stock' => $produit->getStock(),
            'categorie' => $produit->getCategorie()?->getId(),
        ];

        return new JsonResponse($data, 200);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'api_produits_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $produit = new Produit();
        $produit->setTitre($data['titre']);
        $produit->setDescription($data['description'] ?? null);
        $produit->setPrix($data['prix']);
        $produit->setStock($data['stock'] ?? null);

        // Si tu veux charger une catégorie depuis son ID
        // if (isset($data['categorie_id'])) {
        $categorie = $em->getRepository(\App\Entity\Categorie::class)->find(5);
        $produit->setCategorie($categorie);
        // }

        $em->persist($produit);
        $em->flush();

        return new JsonResponse(['message' => 'Produit ajouté', 'titre' => $produit->getTitre()], 201);
    }

    // #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_produits_update', methods: ['PUT', 'PATCH'])]
    public function update(Produit $produit, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $produit->setTitre($data['titre'] ?? $produit->getTitre());
        $produit->setDescription($data['description'] ?? $produit->getDescription());
        $produit->setPrix($data['prix'] ?? $produit->getPrix());
        $produit->setStock($data['stock'] ?? $produit->getStock());

        // if (isset($data['categorie_id'])) {
        // $categorie = $em->getRepository(\App\Entity\Categorie::class)->find(6);
        // $produit->setCategorie($categorie);
        // }

        $em->flush();

        return new JsonResponse(['message' => 'Produit mis à jour']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_produits_delete', methods: ['DELETE'])]
    public function delete(Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($produit);
        $em->flush();

        return new JsonResponse(['message' => 'Produit supprimé']);
    }


}
