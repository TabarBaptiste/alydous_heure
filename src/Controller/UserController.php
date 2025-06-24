<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/user')]
final class UserController extends AbstractController
{
    #[Route('/me', name: 'api_user_me', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getCurrentUser(NormalizerInterface $normalizer): JsonResponse
    {
        $user = $this->getUser();

        // Tu peux personnaliser les groupes de serialization ici
        $data = $normalizer->normalize($user, null, ['groups' => ['user:read']]);

        return $this->json($data);
    }
}
