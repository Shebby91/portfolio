<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'user' => 'User',
            'admin' => false
        ]);
    }

    #[Route('/admin', name: 'app_admin')]
    public function anotherRoute(): Response
    {
        return $this->render('index.html.twig', [
            'user' => 'Admin',
            'admin' => true
        ]);
    }
}
