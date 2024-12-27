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
        $admin = UserFactory::createOne([
            'firstname' => 'Justin',
            'lastname' => 'Massart',
            'username' => 'TheDev',
            'email' => 'justin@mail.com',
            'roles' => ['ROLE_ADMIN'],
        ]);

        $users = UserFactory::createMany(10);

        foreach ($users as $user) {
            $user->_disableAutoRefresh();

            $randomRecipeCount = rand(0, 2);

            RecipeFactory::createMany($randomRecipeCount, function () use ($user) {
                return [
                    'user' => $user,
                ];
            });
        }


        $manager->flush();
    }
}
