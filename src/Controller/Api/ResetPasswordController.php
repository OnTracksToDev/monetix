<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;

#[Route('/api/reset-password')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/request', name: 'api_forgot_password_request', methods: ['POST'])]
    public function request(Request $request, MailerInterface $mailer): JsonResponse
    {
        // deploiement
        $baseUrl = $_ENV['APP_URL'] ?? 'http://127.0.0.1:8000';

        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return $this->json(['error' => "L'adresse e-mail est requise"], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (!$user) {
            return $this->json(['message' => "Si l'adresse e-mail existe, un lien de réinitialisation a été envoyé"]);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['message' => "Si l'adresse e-mail existe, un lien de réinitialisation a été envoyé"]);
        }

        // Envoi du mail
        $emailMessage = (new TemplatedEmail())
            ->from(new Address($_ENV['MAIL_FROM'], 'Support BuvetteApp'))
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetTokenUrl' => "{$baseUrl}/reset-password/{$resetToken->getToken()}",
                'userName' => $user->getNom(),
            ]);
        $mailer->send($emailMessage);

        return $this->json(['message' => "Si l'adresse e-mail existe, un lien de réinitialisation a été envoyé"]);
    }

    #[Route('/reset', name: 'api_reset_password', methods: ['POST'])]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $newPassword = $data['password'] ?? null;

        if (!$token || !$newPassword) {
            return $this->json(['error' => 'Le token et le mot de passe sont requis'], 400);
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['error' => 'Token invalide ou expiré'], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $this->entityManager->flush();

        $this->resetPasswordHelper->removeResetRequest($token);

        return $this->json(['message' => 'Le mot de passe a été réinitialisé avec succès']);
    }
}
