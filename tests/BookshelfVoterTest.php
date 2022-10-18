<?php

namespace App\Tests;

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
}
