<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DisponibiliteRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/disponibilites')]
final class DisponibiliteController extends AbstractController
{
    #[Route('', name: 'disponibilites_semaine', methods: ['GET'])]
    public function getDisponibilitesSemaine(DisponibiliteRepository $repo): JsonResponse
    {
        $dispos = $repo->findBy(['isDisponible' => true]);

        $grouped = [];
        foreach ($dispos as $dispo) {
            $jour = $dispo->getJour();
            $grouped[$jour][] = [
                'id' => $dispo->getId(),
                'start' => $dispo->getStartTime()->format('H:i'),
                'end' => $dispo->getEndTime()->format('H:i'),
            ];
        }

        return $this->json($grouped);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'add_disponibilite', methods: ['POST'])]
    public function addDisponibilite(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $dispo = new Disponibilite();
        $dispo->setJour($data['jour']);
        $dispo->setStartTime(new \DateTime($data['start']));
        $dispo->setEndTime(new \DateTime($data['end']));
        $dispo->setIsDisponible($data['isDisponible'] ?? true);

        $em->persist($dispo);
        $em->flush();

        return $this->json(['message' => 'Créneau ajouté']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_disponibilites_update', methods: ['PUT', 'PATCH'])]
    public function update(Disponibilite $disponibilite, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $disponibilite->setJour($data['jour'] ?? $disponibilite->getJour());
        $disponibilite->setStartTime($data['startTime'] ?? $disponibilite->getStartTime());
        $disponibilite->setEndTime($data['endTime'] ?? $disponibilite->getEndTime());
        $disponibilite->setIsDisponible($data['isDisponible'] ?? $disponibilite->isDisponible());

        $em->flush();

        return new JsonResponse(['message' => 'Disponibilité mis à jour']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_disponibilites_delete', methods: ['DELETE'])]
    public function delete(Disponibilite $disponibilite, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($disponibilite);
        $em->flush();

        return new JsonResponse(['message' => 'Disponibilité supprimée']);
    }


}
