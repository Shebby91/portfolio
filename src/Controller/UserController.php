<?php

namespace App\Controller;

use App\Service\ExampleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function admin(ExampleService $service): Response
    {
        
        //dd($this->getUser());
        //$user = $service->getUserInServiceClass();
        //dd($user);

        return $this->render('user/user.html.twig', [
            'title' => 'User',
        ]);
    }
}
