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
    #[IsGranted('ROLE_USER')]
    public function addReservation(
        Request $request,
        EntityManagerInterface $em,
        PrestationRepository $prestationRepo,
        DisponibiliteRepository $dispoRepo,
        ReservationRepository $reservationRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        // 1) Vérifier la prestation
        $prestation = $prestationRepo->find($data['prestation_id'] ?? null);
        if (!$prestation) {
            return new JsonResponse(['error' => 'Prestation introuvable.'], 404);
        }

        // 2) Construire les DateTime demandés
        try {
            $date = new \DateTime($data['date']);
            $heure = new \DateTime($data['startTime']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Format de date/heure invalide.'], 400);
        }
        $startDateTime = new \DateTime($date->format('Y-m-d') . ' ' . $heure->format('H:i'));
        $duration = $prestation->getDuree();
        $endDateTime = (clone $startDateTime)->modify("+{$duration} minutes");

        // 3) Récupérer toutes les plages dispos du jour
        $joursFr = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];
        $jourKey = $joursFr[$date->format('l')] ?? $date->format('l');
        $plages = $dispoRepo->findBy([
            'jour' => $jourKey,
            'isDisponible' => true,
        ]);

        if (empty($plages)) {
            return new JsonResponse(['error' => 'Aucune disponibilité ce jour-là.'], 400);
        }

        // 4) Vérifier que le créneau demandé est **dans au moins une** des plages
        $valid = false;
        foreach ($plages as $plage) {
            $plageStart = new \DateTime($date->format('Y-m-d') . ' ' . $plage->getStartTime()->format('H:i'));
            $plageEnd = new \DateTime($date->format('Y-m-d') . ' ' . $plage->getEndTime()->format('H:i'));

            if ($startDateTime >= $plageStart && $endDateTime <= $plageEnd) {
                $valid = true;
                break;
            }
        }
        if (!$valid) {
            return new JsonResponse(['error' => 'Le créneau est en dehors des heures disponibles.'], 400);
        }

        // 5) Vérifier les conflits avec d’autres réservations
        $conflicts = $reservationRepo->findConflictingReservations($date, $startDateTime, $endDateTime);
        if (count($conflicts) > 0) {
            return new JsonResponse(['error' => 'Ce créneau est déjà réservé.'], 400);
        }

        // 6) Enregistrer la réservation
        $reservation = new Reservation();
        $reservation
            ->setUser($user)
            ->setPrestation($prestation)
            ->setDate($date)
            ->setStartTime($startDateTime)
            ->setEndTime($endDateTime)
            ->setStatut(Statut::EN_ATTENTE);

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['message' => 'Réservation enregistrée.'], 201);
    }

    // #[Route('', name: 'get_reservations_by_date', methods: ['GET'])]
    // public function getReservationsByDate(Request $request, ReservationRepository $repo): JsonResponse
    // {
    //     $dateParam = $request->query->get('date');
    //     if (!$dateParam) {
    //         return new JsonResponse(['error' => 'Paramètre date manquant'], 400);
    //     }
    //     try {
    //         $date = new \DateTime($dateParam);
    //     } catch (\Exception $e) {
    //         return new JsonResponse(['error' => 'Format de date invalide'], 400);
    //     }

    //     $reservations = $repo->findBy(['date' => $date]);

    //     // On renvoie seulement start/end pour filtrage
    //     $slots = array_map(fn(Reservation $r) => [
    //         'start' => $r->getStartTime()->format('H:i'),
    //         'end' => $r->getEndTime()->format('H:i'),
    //     ], $reservations);

    //     return new JsonResponse($slots);
    // }

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
