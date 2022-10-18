<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Bookshelf;
use App\Entity\Publisher;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $authors = [];
        $publishers = [];
        $bookshelves = [];

        for ($i = 0; $i < 25; $i++) {
            $author = new Author();
            $author->setName($faker->name());
            $authors[$i] = $author;
            $manager->persist($author);

            $publisher = new Publisher();
            $publisher->setName($faker->company());
            $publishers[$i] = $publisher;
            $manager->persist($publisher);
        }

        for ($i = 0; $i < 12; $i++) {
            $bookshelf = new Bookshelf();
            $bookshelf->setName($faker->words(rand(2, 4), true))
                ->setDescription($faker->paragraphs(3, true))
                ->setPublic($faker->boolean())
                ->setOwner($this->userRepository->findOneBy(['username' => array_rand(['sophie' => 'sophie', 'brice' => 'brice', 'gamora' => 'gamora'])]));
            $bookshelves[$i] = $bookshelf;
            $manager->persist($bookshelf);
        }

        for ($i = 0; $i < 260; $i++) {
            $book = new Book();
            $book->setTitle($faker->sentence())
                ->setDescription($faker->paragraphs(rand(2,5), true))
                ->setIsbn($faker->isbn13())
                ->setPages(rand(120, 1200))
                ->setPublicationDate($faker->date('Y'))
                ->setPublisher($publishers[rand(0,24)])
                ->addAuthor($authors[rand(0, 24)]);
            $manager->persist($book);
        }


        $manager->flush();
    }
}
