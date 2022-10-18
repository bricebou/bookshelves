<?php

namespace App\Tests;

use App\Entity\Book;
use App\Entity\Bookshelf;
use App\Repository\BookRepository;
use App\Repository\BookshelfRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookshelfVoterTest extends WebTestCase
{
    /**
     * @var BookRepository
     */
    private $bookshelfRepository;

    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->bookshelfRepository = $this->getContainer()->get(BookshelfRepository::class);
    }

    public function testPublicBookshelf(): void
    {
        /**
         * @var Bookshelf
         */
        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => true]);

        $crawler = $this->client->request('GET', '/bookshelf/' . $bookshelf->getUlid());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $bookshelf->getName());
    }

    public function testPrivateBookshelfWithoutAuthentication()
    {
        /**
         * @var Bookshelf
         */
        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => false]);

        $crawler = $this->client->request('GET', '/bookshelf/' . $bookshelf->getUlid());

        $loginRoute = static::getContainer()->get('router')->generate('bks_login', array(), false);

        $this->assertResponseRedirects($loginRoute);
    }

    public function testPrivateBookshelfWithOwnerAuthenticated()
    {
        /**
         * @var Bookshelf
         */
        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => false]);

        $this->client->loginUser($bookshelf->getOwner());

        $crawler = $this->client->request('GET', '/bookshelf/' . $bookshelf->getUlid());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $bookshelf->getName());
    }

    public function testPrivateBookshelfWithUserNotOwnerAuthenticated()
    {
        /**
         * @var UserRepository
         */
        $userRepository = $this->getContainer()->get(UserRepository::class);

        /**
         * @var User
         */
        $userLoggedIn = $userRepository->findOneBy(['username' => 'obi']);

        $this->client->loginUser($userLoggedIn);

        /**
         * @var User
         */
        $owner = $userRepository->findOneBy(['username' => 'sophie']);

        $bookshelf = $this->bookshelfRepository->findOneBy(['owner' => $owner, 'public' => false]);

        $crawler = $this->client->request('GET', '/bookshelf/' . $bookshelf->getUlid());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testBookFromPublicBookshelf()
    {
        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => true]);

        /**
         * @var BookRepository
         */
        $bookRepository = $this->getContainer()->get(BookRepository::class);

        /**
         * @var Book
         */
        $book = $bookRepository->findOneBy(['bookshelf' => $bookshelf]);

        $crawler = $this->client->request('GET', '/book/' . $book->getUlid());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h1', $book->getTitle());
    }

    public function testBookFromPrivateBookshelf() {
        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => false]);

        /**
         * @var BookRepository
         */
        $bookRepository = $this->getContainer()->get(BookRepository::class);

        /**
         * @var Book
         */
        $book = $bookRepository->findOneBy(['bookshelf' => $bookshelf]);

        $crawler = $this->client->request('GET', '/book/' . $book->getUlid());

        $loginRoute = static::getContainer()->get('router')->generate('bks_login', array(), false);

        $this->assertResponseRedirects($loginRoute);
    }

    public function testBookFromPrivateBookshelfWithUserNotOwnerAuthenticated()
    {
        /**
         * @var BookRepository
         */
        $bookRepository = $this->getContainer()->get(BookRepository::class);

        /**
         * @var UserRepository
         */
        $userRepository = $this->getContainer()->get(UserRepository::class);

        /**
         * @var User
         */
        $userLoggedIn = $userRepository->findOneBy(['username' => 'obi']);

        /**
         * @var User
         */
        $owner = $userRepository->findOneBy(['username' => 'sophie']);

        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => false, 'owner' => $owner]);

        $book = $bookRepository->findOneBy(['bookshelf' => $bookshelf]);

        $this->client->loginUser($userLoggedIn);
        $crawer = $this->client->request('GET', '/book/' . $book->getUlid());

        $this->assertResponseStatusCodeSame(403);
    }

    public function testBookFromPrivateBookshelfWithOwnerAuthenticated()
    {
        /**
         * @var BookRepository
         */
        $bookRepository = $this->getContainer()->get(BookRepository::class);

        /**
         * @var UserRepository
         */
        $userRepository = $this->getContainer()->get(UserRepository::class);

        /**
         * @var User
         */
        $owner = $userRepository->findOneBy(['username' => 'sophie']);

        $bookshelf = $this->bookshelfRepository->findOneBy(['public' => false, 'owner' => $owner]);

        $book = $bookRepository->findOneBy(['bookshelf' => $bookshelf]);

        $this->client->loginUser($owner);
        $crawer = $this->client->request('GET', '/book/' . $book->getUlid());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextSame('h1', $book->getTitle());
    }
}
