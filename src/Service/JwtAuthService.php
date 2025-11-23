<?php
// src/Service/JwtAuthService.php

namespace App\Service;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtAuthService
{
    public function __construct(
        private JWTTokenManagerInterface $jwtManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function generateToken(UserInterface $user): string
    {
        return $this->jwtManager->create($user);
    }

    public function verifyCredentials(User $user, string $password): bool
    {
        return $this->passwordHasher->isPasswordValid($user, $password);
    }
}