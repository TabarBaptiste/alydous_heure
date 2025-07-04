<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
final class UserController extends AbstractController
{
    #[Route('/user/me', name: 'api_user_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getCurrentUser(NormalizerInterface $normalizer): JsonResponse
    {
        $user = $this->getUser();

        // Tu peux personnaliser les groupes de serialization ici
        $data = $normalizer->normalize($user, null, ['groups' => ['user:read']]);

        return $this->json($data);
    }

    #[Route('/admin/user', name: 'admin_users_list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function listAllUsers(UserRepository $userRepo): JsonResponse
    {
        $users = $userRepo->findAll();

        $data = array_map(function ($user) {
            return [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'email' => $user->getEmail(),
                'reservationsCount' => $user->getReservations()->count(),
                'achatsCount' => $user->getAchats()->count(),
            ];
        }, $users);

        return new JsonResponse($data);
    }

    #[Route('/admin/user/{id}', name: 'api_user_by_id', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getUserById(int $id, UserRepository $userRepo, NormalizerInterface $normalizer): JsonResponse
    {
        $user = $userRepo->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        $data = $normalizer->normalize($user, null, ['groups' => ['user:read']]);
        $data['nb_reservations'] = count($user->getReservations());
        $data['nb_achats'] = count($user->getAchats());

        return $this->json($data);
    }

    #[Route('/admin/user/{id}', name: 'admin_user_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id, UserRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $user = $repo->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur introuvable'], 404);
        }

        $em->remove($user);
        $em->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé']);
    }

}
