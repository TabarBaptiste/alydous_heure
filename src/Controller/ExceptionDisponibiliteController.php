<?php

namespace App\Controller;

use App\Entity\ExceptionDisponibilite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\ExceptionDisponibiliteRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/exceptions')]
final class ExceptionDisponibiliteController extends AbstractController
{
    #[Route('', name: 'get_exceptions', methods: ['GET'])]
    public function getAll(ExceptionDisponibiliteRepository $repo): JsonResponse
    {
        $exceptions = $repo->findAll();
        dd($exceptions);
        $data = array_map(fn($e) => [
            'id' => $e->getId(),
            'date' => $e->getDate()->format('Y-m-d'),
            'start' => $e->getStartTime()?->format('H:i'),
            'end' => $e->getEndTime()?->format('H:i'),
            'is_disponible' => $e->isDisponible(),
            'commentaire' => $e->getCommentaire(),
        ], $exceptions);

        return $this->json($data);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'add_exception', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $exception = new ExceptionDisponibilite();
        $exception->setDate(new \DateTime($data['date']));
        $exception->setStartTime(isset($data['start']) ? new \DateTime($data['start']) : null);
        $exception->setEndTime(isset($data['end']) ? new \DateTime($data['end']) : null);
        $exception->setIsDisponible((bool) $data['is_disponible']);
        $exception->setCommentaire($data['commentaire'] ?? null);

        $em->persist($exception);
        $em->flush();

        return $this->json(['message' => 'Exception ajoutée']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'delete_exception', methods: ['DELETE'])]
    public function delete(ExceptionDisponibilite $exception, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($exception);
        $em->flush();

        return $this->json(['message' => 'Exception supprimée']);
    }
}
