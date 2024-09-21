<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne(['email'=> 'admin@test.com', 'plainPassword' => 'sqz8FxvrGvAWUDT', 'firstName' => 'Example', 'lastName' => 'Admin', 'roles' => ['ROLE_ADMIN']]);
        UserFactory::createOne(['email'=> 'user@test.com', 'plainPassword' => 'FcjF5fe4zi2SLuH', 'firstName' => 'Example', 'lastName' => 'User']);
        UserFactory::createMany(20);
        $manager->flush();
    }
}