<?php
// src/Controller/AuthController.php

namespace App\Controller;

use App\Entity\User;
use App\Service\JwtAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {

        // 🔐 Vérification clé secrète
        $secret = $request->headers->get('X-REGISTRATION-KEY');
        if ($secret !== $_ENV['REGISTER_SECRET']) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setNom($data['nom'] ?? '');
        $user->setPassword($passwordHasher->hashPassword($user, $data['password'] ?? ''));
        $user->setRoles(['ROLE_USER']);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return new JsonResponse(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($user);
        $em->flush();

        return new JsonResponse([
            'message' => 'Utilisateur créé avec succès',
            'user' => json_decode($serializer->serialize($user, 'json', ['groups' => 'user']))
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        JwtAuthService $jwtAuthService,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email et mot de passe requis'], Response::HTTP_BAD_REQUEST);
        }

        // Recherche de l'utilisateur
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$jwtAuthService->verifyCredentials($user, $password)) {
            return new JsonResponse(['error' => 'Identifiants invalides'], Response::HTTP_UNAUTHORIZED);
        }

        // Génération du token JWT
        $token = $jwtAuthService->generateToken($user);

        return new JsonResponse([
            'token' => $token,
            'user' => json_decode($serializer->serialize($user, 'json', ['groups' => 'user']))
        ], Response::HTTP_OK);
    }

    #[Route('/me', name: 'api_me', methods: ['GET'])]
    public function me(SerializerInterface $serializer): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(
            $serializer->serialize($user, 'json', ['groups' => 'user']),
            Response::HTTP_OK,
            [],
            true
        );
    }
}
