<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Bookshelf;
use App\Entity\Publisher;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userRepository;

    private $hasher;

    public function __construct(UserRepository $userRepository, UserPasswordHasherInterface $hasher)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

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



        $authors = [];
        $publishers = [];
        $bookshelves = [];

        for ($i = 1; $i <= 25; $i++) {
            $author = new Author();
            $author->setName($faker->name());
            $authors[$i] = $author;
            $manager->persist($author);

            $publisher = new Publisher();
            $publisher->setName($faker->company());
            $publishers[$i] = $publisher;
            $manager->persist($publisher);
        }

        for ($i = 1; $i <= 16; $i++) {
            $bookshelf = new Bookshelf();
            $bookshelf->setName($faker->words(rand(2, 4), true))
                ->setDescription($faker->paragraphs(3, true))
                ->setPublic($faker->boolean())
                ->setOwner($this->userRepository->findOneBy([
                    'username' => array_rand(['sophie' => 'sophie', 'brice' => 'brice', 'gamora' => 'gamora'])
                ]));
            $bookshelves[$i] = $bookshelf;
            $manager->persist($bookshelf);
        }

        for ($i = 1; $i <= 260; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence())
                ->setDescription($faker->paragraphs(rand(2, 5), true))
                ->setIsbn($faker->isbn13())
                ->setPages(rand(120, 1200))
                ->setPublicationDate($faker->date('Y'))
                ->setPublisher($publishers[rand(1, 25)])
                ->addAuthor($authors[rand(1, 25)])
                ->setBookshelf($bookshelves[rand(1, 12)]);
            $manager->persist($book);
        }


        $manager->flush();
    }
}
