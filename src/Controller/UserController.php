<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function user(): Response
    {
        $user = $this->getUser();
        
        
        return $this->render('user/user.html.twig', [
            'title' => 'User',
            'user' => $user
        ]);
    }
}
