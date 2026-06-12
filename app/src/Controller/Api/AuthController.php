<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_auth_')]
class AuthController extends AbstractApiController
{
    /**
     * Inscription d'un nouvel utilisateur.
     * POST /api/register  { "email": "...", "password": "..." }
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserRepository $userRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse([
                'error' => ['status' => 422, 'message' => 'email et password sont obligatoires'],
            ], 422);
        }

        if ($userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse([
                'error' => ['status' => 409, 'message' => 'Cet email est déjà utilisé'],
            ], 409);
        }

        $user = new User();
        $user->setEmail($email);
        // On ne stocke JAMAIS le mot de passe en clair : on le hache
        $user->setPassword($hasher->hashPassword($user, $password));

        if ($response = $this->validateOrFail($user)) {
            return $response;
        }

        $em->persist($user);
        $em->flush();

        return $this->json2($user, 201, ['user:read']);
    }

    /**
     * Connexion. Le corps de cette méthode n'est JAMAIS exécuté :
     * le firewall "json_login" (security.yaml) intercepte la requête,
     * vérifie email/password et renvoie un token JWT.
     * La route doit néanmoins exister pour que le routeur connaisse /api/login.
     *
     * POST /api/login  { "email": "...", "password": "..." }
     */
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('Cette méthode est interceptée par le firewall de sécurité.');
    }

    /**
     * Retourne l'utilisateur actuellement connecté (via son token JWT).
     * GET /api/me
     */
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        return $this->json2($this->getUser(), 200, ['user:read']);
    }
}
