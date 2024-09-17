<?php

namespace App\DataFixtures;

use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne(['email'=> 'admin@test.com', 'firstName' => 'Admin', 'roles' => ['ROLE_ADMIN']]);
        UserFactory::createOne(['email'=> 'user@test.com', 'firstName' => 'User']);
        UserFactory::createMany(20);
        $manager->flush();
    }
}
