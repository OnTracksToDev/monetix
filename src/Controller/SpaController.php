<?php

namespace App\Controller;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SpaController extends AbstractController
{
    //     #[Route('/test/email-reset-password', name: 'test_email_preview')]
    //     public function testEmailPreview(): Response
    //     {
    //         $fakeData = [
    //             'userName' => 'Jean',
    //             'resetTokenUrl' => 'http://127.0.0.1:8000/reset-password/fake-token-1234567890',
    //         ];

    //         return $this->render('reset_password/email.html.twig', $fakeData);
    //     }


    #[Route('/test-mail', name: 'test_mail')]
    public function test(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from($_ENV['MAIL_FROM'])
            ->to('dsmoonweb@gmail.com')
            ->subject('Test Mail Brevo')
            ->text('Si tu reçois ce mail, Brevo fonctionne.');

        try {
            $mailer->send($email);
            return new Response("Email envoyé !");
        } catch (\Throwable $e) {
            return new Response("Erreur: " . $e->getMessage());
        }
    }

    // Catch-all exclut route API
    #[Route('/{path}', name: 'spa', requirements: ['path' => '^(?!api).+'])]
    public function index(string $path = ''): Response
    {
        return $this->render('base.html.twig');
    }
}
