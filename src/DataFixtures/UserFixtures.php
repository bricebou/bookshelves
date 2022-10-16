<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('gamora')
            ->setPassword($this->hasher->hashPassword($user, 'dev'))
            ->setEmail('gamora@example.com')
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPublic(false);
        $manager->persist($user);
        $user = new User();
        $user->setUsername('sophie')
            ->setPassword($this->hasher->hashPassword($user, 'dev'))
            ->setEmail('sophie@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPublic(false);
        $manager->persist($user);
        $user = new User();
        $user->setUsername('brice')
            ->setPassword($this->hasher->hashPassword($user, 'dev'))
            ->setEmail('brice@example.com')
            ->setRoles(['ROLE_ADMIN'])
            ->setPublic(true);
        $manager->persist($user);
        $user = new User();
        $user->setUsername('obi')
            ->setPassword($this->hasher->hashPassword($user, 'dev'))
            ->setEmail('obi@example.com')
            ->setRoles(['ROLE_USER'])
            ->setPublic(true);
        $manager->persist($user);
        $user = new User();
        $user->setUsername('salto')
            ->setPassword($this->hasher->hashPassword($user, 'dev'))
            ->setEmail('salto@example.com')
            ->setRoles(['ROLE_USER'])
            ->setPublic(false);
        $manager->persist($user);

        $manager->flush();
    }
}
