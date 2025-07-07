<?php

namespace App\Controller;

use App\Entity\Disponibilite;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\DisponibiliteRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\ExceptionDisponibiliteRepository;
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

    #[Route('/all', name: 'disponibilites', methods: ['GET'])]
    public function getDisponibilites(DisponibiliteRepository $repo): JsonResponse
    {
        $dispos = $repo->findAll();

        $grouped = [];
        foreach ($dispos as $dispo) {
            $jour = $dispo->getJour();
            $grouped[$jour][] = [
                'id' => $dispo->getId(),
                'start' => $dispo->getStartTime()->format('H:i'),
                'end' => $dispo->getEndTime()->format('H:i'),
                'is_disponible' => $dispo->isDisponible(),
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

        if (isset($data['startTime'])) {
            $disponibilite->setStartTime(new \DateTime($data['startTime']));
        }

        if (isset($data['endTime'])) {
            $disponibilite->setEndTime(new \DateTime($data['endTime']));
        }

        $disponibilite->setIsDisponible($data['isDisponible'] ?? $disponibilite->isDisponible());

        $em->flush();

        return new JsonResponse(['message' => 'Disponibilité mise à jour']);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'api_disponibilites_delete', methods: ['DELETE'])]
    public function delete(Disponibilite $disponibilite, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($disponibilite);
        $em->flush();

        return new JsonResponse(['message' => 'Disponibilité supprimée']);
    }

    #[Route('/availability-overview', methods: ['GET'])]
    public function overview(
        Request $rq,
        DisponibiliteRepository $dRepo,
        ExceptionDisponibiliteRepository $eRepo
    ): JsonResponse {
        $start = new \DateTime($rq->query->get('start'));
        $end = new \DateTime($rq->query->get('end'));

        // 1) Récupérer les règles hebdo
        $allDispo = $dRepo->findBy(['isDisponible' => true]);
        // indexer par jour-fr : Lundi → [ {start,end},… ]
        $weekly = [];
        foreach ($allDispo as $d) {
            $weekly[$d->getJour()][] = [
                'start' => $d->getStartTime()->format('H:i'),
                'end' => $d->getEndTime()->format('H:i'),
            ];
        }

        // 2) Récupérer toutes les exceptions entre start et end
        $exceptions = $eRepo->findBetween($start, $end); // à implémenter
        // grouper par date
        $excByDate = [];
        foreach ($exceptions as $ex) {
            $type = $ex->isDisponible() ? 'add' : 'remove';
            $excByDate[$ex->getDate()->format('Y-m-d')][] = [
                'type' => $type,
                'start' => $ex->getStartTime()?->format('H:i'),
                'end' => $ex->getEndTime()?->format('H:i'),
                'commentaire' => $ex->getCommentaire(),
            ];
        }

        // 3) Parcourir chaque date dans l’intervalle
        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->modify('+1 day'));
        $output = [];
        foreach ($period as $day) {
            $jsDate = $day->format('Y-m-d');
            $frDay = $day->format('l');            // English day name
            // ou ta map pour obtenir Lundi, Mardi…
            $weeklySlots = $weekly[$frDay] ?? [];
            $output[] = [
                'date' => $jsDate,
                'weeklySlots' => $weeklySlots,
                'exceptions' => $excByDate[$jsDate] ?? []
            ];
        }

        return $this->json($output);
    }

}
