<?php

namespace App\Controller;

use App\Service\ExampleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function admin(ExampleService $service): Response
    {
        
        //dd($this->getUser());
        $user = $service->getUserInServiceClass();
        dd($user);

        return $this->render('admin/admin.html.twig', [
            'title' => 'Admin',
        ]);
    }
}
