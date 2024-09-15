<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function login(EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'admin@test.com']);
        dd($user);
        
        return $this->render('security/login.html.twig', [
            'title' => 'Login',
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig', [
            'title' => 'Registraion',
        ]);
    }
}
