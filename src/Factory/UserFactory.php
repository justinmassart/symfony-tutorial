<?php

namespace App\Factory;

use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

final class UserFactory extends PersistentProxyObjectFactory
{

    private UserPasswordHasherInterface $passwordHasher;
    private SluggerInterface $slugger;

    public function __construct(UserPasswordHasherInterface $passwordHasher, SluggerInterface $slugger)
    {
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $timestamp = DateTimeImmutable::createFromMutable(self::faker()->dateTime());

        $user = new User();
        $plaintextPassword = 'password';
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plaintextPassword);

        $firstname = self::faker()->firstName();
        $lastname = self::faker()->lastname();
        $username = $this->slugger->slug($firstname.$lastname);

        return [
            'createdAt' => $timestamp,
            'deletedAt' => null,
            'email' => self::faker()->unique()->email(),
            'firstname' => $firstname,
            'lastname' => $lastname,
            'password' => $hashedPassword,
            'roles' => ['ROLE_USER'],
            'updatedAt' => $timestamp,
            'username' => $username,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this;
    }
}
