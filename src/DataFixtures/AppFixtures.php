<?php

namespace App\DataFixtures;

use App\Factory\RecipeFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createOne([
            'firstname' => 'Justin',
            'lastname' => 'Massart',
            'username' => 'TheDev',
            'email' => 'justin@mail.com',
            'roles' => ['ROLE_ADMIN'],
        ]);
        UserFactory::createMany(25);
        RecipeFactory::createMany(25);

        $manager->flush();
    }
}
