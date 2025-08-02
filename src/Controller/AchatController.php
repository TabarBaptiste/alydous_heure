<?php

namespace App\Controller;

use App\Enum\Statut;
use App\Entity\Achat;
use App\Repository\AchatRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/achats')]
final class AchatController extends AbstractController
{
    #[Route('', name: 'add_achat', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function addAchat(Request $request, EntityManagerInterface $em, ProduitRepository $produitRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $produit = $produitRepo->find($data['produit_id']);

        if (!$produit) {
            return new JsonResponse(['message' => 'Produit non trouvé.'], 404);
        } elseif ($produit->getStock() < $data['quantite']) {
            return new JsonResponse(['message' => 'Stock insuffisant.'], 400);
        } elseif ($produit->getStock() === 0) {
            return new JsonResponse(['message' => 'Produit en rupture de stock.'], 400);
        }

        $achat = new Achat();
        $achat->setUser($user);
        $achat->setProduit($produit);
        $achat->setQuantite($data['quantite']);
        $achat->setDateAchat(new \DateTime());
        $achat->setStatut(Statut::EN_ATTENTE);

        $produit->setStock($produit->getStock() - $data['quantite']);

        $em->persist($achat);
        $em->flush();

        return new JsonResponse(['message' => 'Achat enregistré.'], 201);
    }

    #[Route('/mine', name: 'get_my_achats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getMyAchats(AchatRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $achats = $repo->findBy(['user' => $user]);

        return $this->json($achats, 200, [], ['groups' => 'achat:read']);
    }

    #[Route('', name: 'get_all_achats', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllAchats(Request $request, AchatRepository $repo): JsonResponse
    {
        $userId = $request->query->get('userId');
        $statut = $request->query->get('statut');
        $date = $request->query->get('date');

        try {
            $qb = $repo->createQueryBuilder('a');

            // Filtre par date (entre 00:00 et 23:59 du jour donné)
            if ($date) {
                $dateStart = new \DateTime($date . ' 00:00:00');
                $dateEnd = new \DateTime($date . ' 23:59:59');
                $qb->andWhere('a.dateAchat BETWEEN :start AND :end')
                    ->setParameter('start', $dateStart)
                    ->setParameter('end', $dateEnd);
            }

            // Filtre par utilisateur
            if ($userId) {
                $qb->andWhere('a.user = :userId')
                    ->setParameter('userId', $userId);
            }

            // Filtre par statut
            if ($statut) {
                $qb->andWhere('a.statut = :statut')
                    ->setParameter('statut', $statut);
            }

            // Tri par date décroissante
            $qb->orderBy('a.dateAchat', 'DESC');

            $achats = $qb->getQuery()->getResult();

            return $this->json($achats, 200, [], ['groups' => 'achat:read']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Paramètres invalides ou erreur de traitement.'], 400);
        }
    }

    #[Route('/{id}/statut', name: 'change_achat_statut', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changeAchatStatut(int $id, Request $request, AchatRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $achat = $repo->find($id);
        if (!$achat) {
            return new JsonResponse(['message' => 'Achat non trouvé.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        $newStatut = $data['statut'] ?? null;
        $statutValues = array_map(fn($case) => $case->value, Statut::cases());

        if (!in_array($newStatut, $statutValues)) {
            return new JsonResponse(['message' => 'Statut invalide'], Response::HTTP_BAD_REQUEST);
        }

        $achat->setStatut(Statut::from($newStatut));
        $em->flush();

        return new JsonResponse(['message' => 'Statut mis à jour.']);
    }

}
