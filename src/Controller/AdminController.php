<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\TwigBundle\DependencyInjection\Compiler\TwigEnvironmentPass;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(UserRepository $userRepository, Mailerservice $mailer): Response
    {
        $this->getUser();
        
        $mailer->sendEmail($this->getUser());
        $users = $userRepository->findAll();
        //dd($userRepository->findAll());
        return $this->render('admin/admin.html.twig', [
            'title' => 'Admin',
            'users' => $users
        ]);
    }

    #[Route('/test-email-test')]
public function testEmail(): Response
{
    $this->getUser();

    return $this->render('email/base_mail.html.twig', ['user' => $this->getUser()]);
}
}
