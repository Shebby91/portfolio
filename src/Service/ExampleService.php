<?php
namespace App\Service;

use Symfony\Bundle\SecurityBundle\Security;

class ExampleService
{

    private Security $security;
    
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getUserInServiceClass(): string
    {
        return $this->security->getUser()->getUserIdentifier();
    }
}