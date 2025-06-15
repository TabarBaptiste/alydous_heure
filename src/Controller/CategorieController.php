<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Enum\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CategorieController extends AbstractController
{
    #[Route('/api/categories', name: 'get_all_categories', methods: ['GET'])]
    public function getAllCategories(CategorieRepository $repo): JsonResponse
    {
        $categories = $repo->findAll();
        return $this->json($categories, 200, [], ['groups' => 'categorie:read']);
    }

    #[Route('/api/categories', name: 'add_category', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function addCategory(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $categorie = new Categorie();
        $categorie->setNom($data['nom']);

        // Pour l'enum, convertir la chaîne reçue en instance CategorieType
        try {
            $categorie->setType(CategorieType::from($data['type']));
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Type invalide'], 400);
        }

        $em->persist($categorie);
        $em->flush();

        return $this->json($categorie, 201, [], ['groups' => 'categorie:read']);
    }

    #[Route('/api/categories/{id}', name: 'update_category', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateCategory(int $id, Request $request, CategorieRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $categorie = $repo->find($id);
        if (!$categorie) {
            return $this->json(['error' => 'Catégorie non trouvée'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['nom'])) {
            $categorie->setNom($data['nom']);
        }

        if (isset($data['type'])) {
            try {
                $categorie->setType(CategorieType::from($data['type']));
            } catch (\ValueError $e) {
                return $this->json(['error' => 'Type invalide'], 400);
            }
        }

        $em->flush();

        return $this->json($categorie, 200, [], ['groups' => 'categorie:read']);
    }

    #[Route('/api/categories/{id}', name: 'delete_category', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteCategory(int $id, CategorieRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $categorie = $repo->find($id);
        if (!$categorie) {
            return $this->json(['error' => 'Catégorie non trouvée'], 404);
        }

        // Vérifier qu'il n'y a pas de prestations ou produits liés (optionnel)

        $em->remove($categorie);
        $em->flush();

        return $this->json(['message' => 'Catégorie supprimée'], 200);
    }

    #[Route('/api/categories/nom/{nom}', name: 'get_categories_by_nom', methods: ['GET'])]
    public function getCategoriesByNom(string $nom, CategorieRepository $repo): JsonResponse
    {
        $categories = $repo->findBy(['nom' => $nom]);

        return $this->json($categories, 200, [], ['groups' => 'categorie:read']);
    }

    #[Route('/api/categories/type/{type}', name: 'get_categories_by_type', methods: ['GET'])]
    public function getCategoriesByType(string $type, CategorieRepository $repo): JsonResponse
    {
        try {
            $typeEnum = CategorieType::from($type);
        } catch (\ValueError $e) {
            return $this->json(['error' => 'Type invalide. Utilisez PRODUIT ou PRESTATION.'], 400);
        }

        $categories = $repo->findBy(['type' => $typeEnum]);

        return $this->json($categories, 200, [], ['groups' => 'categorie:read']);
    }

    #[Route('/api/categories/search/{term}', name: 'search_categories_by_nom', methods: ['GET'])]
    public function searchCategories(string $term, CategorieRepository $repo): JsonResponse
    {
        $qb = $repo->createQueryBuilder('c');
        $qb->where('LOWER(c.nom) LIKE :term')
            ->setParameter('term', '%' . strtolower($term) . '%');

        $categories = $qb->getQuery()->getResult();

        return $this->json($categories, 200, [], ['groups' => 'categorie:read']);
    }


}
