<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Enum\Statut;
use App\Repository\PrestationRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\DisponibiliteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/reservation')]
class ReservationController extends AbstractController
{
    #[Route('', name: 'add_reservation', methods: ['POST'])]
    #[IsGranted('ROLE_USER')] public function addReservation(
        Request $request,
        EntityManagerInterface $em,
        PrestationRepository $prestationRepo,
        DisponibiliteRepository $dispoRepo,
        ReservationRepository $reservationRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $prestation = $prestationRepo->find($data['prestation_id']);
        if (!$prestation) {
            return new JsonResponse(['error' => 'Prestation introuvable.'], 404);
        }

        $date = new \DateTime($data['date']);
        $startTime = new \DateTime($data['startTime']);

        // Récupérer la disponibilité du jour
        $joursFr = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];
        $jour = $joursFr[$date->format('l')] ?? $date->format('l');
        $disponibilite = $dispoRepo->findOneBy(['jour' => $jour, 'isDisponible' => true]);
        if (!$disponibilite) {
            return new JsonResponse(['error' => 'Aucune disponibilité ce jour-là.'], 400);
        }

        // Fusionner date + heure demandée
        $startDateTime = new \DateTime($date->format('Y-m-d') . ' ' . $startTime->format('H:i'));
        $duration = $prestation->getDuree();
        $endDateTime = (clone $startDateTime)->modify("+$duration minutes");

        // Vérifier si le créneau demandé est dans la plage de disponibilité
        $dispoStart = new \DateTime($date->format('Y-m-d') . ' ' . $disponibilite->getStartTime()->format('H:i'));
        $dispoEnd = new \DateTime($date->format('Y-m-d') . ' ' . $disponibilite->getEndTime()->format('H:i'));

        if ($startDateTime < $dispoStart || $endDateTime > $dispoEnd) {
            return new JsonResponse(['error' => 'Le créneau est en dehors des heures disponibles.'], 400);
        }

        // Vérifier les conflits de réservation
        $conflicts = $reservationRepo->findConflictingReservations($date, $startDateTime, $endDateTime);
        if (count($conflicts) > 0) {
            return new JsonResponse(['error' => 'Ce créneau est déjà réservé.'], 400);
        }

        // Enregistrer
        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setPrestation($prestation);
        $reservation->setDate($date);
        $reservation->setStartTime($startDateTime);
        $reservation->setEndTime($endDateTime);
        $reservation->setStatut(Statut::EN_ATTENTE);

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['message' => 'Réservation enregistrée.'], 201);
    }


    #[Route('/mine', name: 'get_my_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMyReservations(ReservationRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $reservations = $repo->findBy(['user' => $user]);

        return $this->json($reservations, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('/{id}', name: 'get_reservations_by_id', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getReservationsById(int $id, Reservation $reservation): JsonResponse
    {
        $data = [
            // 'id' => $reservation->getId(),
            'user' => $reservation->getUser(),
            'prestation' => $reservation->getPrestation(),
            'date' => $reservation->getDate(),
            'startTime' => $reservation->getStartTime(),
            'endTime' => $reservation->getEndTime(),
            'statut' => $reservation->getStatut(),
        ];

        return $this->json($data, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('', name: 'get_all_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllReservations(ReservationRepository $repo): JsonResponse
    {
        $reservations = $repo->findAll();

        return $this->json($reservations, 200, [], ['groups' => 'reservation:read']);
    }

    #[Route('/{id}', name: 'update_reservation', methods: ['PUT'])]
    #[IsGranted('ROLE_USER')]
    public function updateReservation(int $id, Request $request, ReservationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $reservation = $repo->find($id);
        if (!$reservation) {
            return new JsonResponse(['message' => 'Réservation introuvable'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if ($reservation->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);
        $reservation->setDate(new \DateTime($data['date']));
        $reservation->setStartTime(new \DateTime($data['startTime']));
        $reservation->setEndTime(new \DateTime($data['endTime']));

        $em->flush();

        return new JsonResponse(['message' => 'Réservation modifiée']);
    }

    #[Route('/{id}', name: 'delete_reservation', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function deleteReservation(int $id, ReservationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $reservation = $repo->find($id);
        if (!$reservation) {
            return new JsonResponse(['message' => 'Réservation introuvable'], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        if ($reservation->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['message' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $em->remove($reservation);
        $em->flush();

        return new JsonResponse(['message' => 'Réservation supprimée']);
    }

    #[Route('/{id}/statut', name: 'change_reservation_statut', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeStatut(int $id, Request $request, ReservationRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $reservation = $repo->find($id);
        if (!$reservation) {
            return new JsonResponse(['message' => 'Réservation introuvable'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $newStatut = $data['statut'] ?? null;
        $statutValues = array_map(fn($case) => $case->value, Statut::cases());

        if (!in_array($newStatut, $statutValues)) {
            return new JsonResponse(['message' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
        }

        $reservation->setStatut(Statut::from($newStatut));
        $em->flush();

        return new JsonResponse(['message' => 'Statut mis à jour']);
    }
}
