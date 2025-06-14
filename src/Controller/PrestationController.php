<?php

namespace App\Controller;

use App\Entity\Prestation;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/prestation')]
final class PrestationController extends AbstractController
{
    #[Route('', name: 'api_prestations', methods: ['GET'])]
    public function index(Request $request, PrestationRepository $prestationRepository): JsonResponse
    {
        $categorieId = $request->query->get('categorie');

        if ($categorieId) {
            $prestations = $prestationRepository->findBy(['categorie' => $categorieId]);
        } else {
            $prestations = $prestationRepository->findAll();
        }

        $data = array_map(fn(Prestation $p) => [
            'id' => $p->getId(),
            'titre' => $p->getTitre(),
            'description' => $p->getDescription(),
            'prix' => $p->getPrix(),
            'duree' => $p->getDuree(),
            'categorie' => $p->getCategorie()?->getId(),
        ], $prestations);

        return new JsonResponse($data, 200);
    }

    #[Route('/{id}', name: 'api_prestations_show', methods: ['GET'])]
    public function show(Prestation $prestation): JsonResponse
    {
        $data = [
            'id' => $prestation->getId(),
            'titre' => $prestation->getTitre(),
            'description' => $prestation->getDescription(),
            'prix' => $prestation->getPrix(),
            'duree' => $prestation->getDuree(),
            'categorie' => $prestation->getCategorie()?->getId(),
        ];

        return new JsonResponse($data, 200);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'api_prestations_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $prestation = new Prestation();
        $prestation->setTitre($data['titre']);
        $prestation->setDescription($data['description'] ?? null);
        $prestation->setPrix($data['prix']);
        $prestation->setDuree($data['duree'] ?? null);

        // Si tu veux charger une catégorie depuis son ID
        // if (isset($data['categorie_id'])) {
        $categorie = $em->getRepository(\App\Entity\Categorie::class)->find(5);
        $prestation->setCategorie($categorie);
        // }

        $em->persist($prestation);
        $em->flush();

        return new JsonResponse(['message' => 'Prestation ajouté', 'titre' => $prestation->getTitre()], 201);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_prestations_update', methods: ['PUT', 'PATCH'])]
    public function update(Prestation $prestation, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $prestation->setTitre($data['titre'] ?? $prestation->getTitre());
        $prestation->setDescription($data['description'] ?? $prestation->getDescription());
        $prestation->setPrix($data['prix'] ?? $prestation->getPrix());
        $prestation->setDuree($data['duree'] ?? $prestation->getDuree());

        // if (isset($data['categorie_id'])) {
        // $categorie = $em->getRepository(\App\Entity\Categorie::class)->find(5);
        // $prestation->setCategorie($categorie);
        // }

        $em->flush();

        return new JsonResponse(['message' => 'Prestation mis à jour']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_prestations_delete', methods: ['DELETE'])]
    public function delete(Prestation $prestation, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($prestation);
        $em->flush();

        return new JsonResponse(['message' => 'Prestation supprimé']);
    }


}
