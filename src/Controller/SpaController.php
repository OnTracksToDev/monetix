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


    // Catch-all exclut route API
    #[Route('/{path}', name: 'spa', requirements: ['path' => '^(?!api).+'])]
    public function index(string $path = ''): Response
    {
        return $this->render('base.html.twig');
    }
}
