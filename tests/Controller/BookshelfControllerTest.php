<?php

namespace App\Test\Controller;

use App\Entity\Bookshelf;
use App\Entity\User;
use App\Repository\BookshelfRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Router;

class BookshelfControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private BookshelfRepository $repository;
    private UserRepository $userRepository;
    private Router $router;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        $this->repository = $container->get('doctrine')->getRepository(Bookshelf::class);

        $this->userRepository = $container->get('doctrine')->getRepository(User::class);
        $this->user = $this->userRepository->findOneBy(['username' => 'brice']);

        $this->router = $container->get('router');
    }


    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', $this->router->generate('bks_bookshelf_create'));

        // Checking the Voter => redirect to login if unauthenticated
        self::assertResponseStatusCodeSame(302);

        // On s'authentifie et on vérifie qu'on accède bien à la page
        $this->client->loginUser($this->user);
        $this->client->request('GET', $this->router->generate('bks_bookshelf_create'));
        self::assertResponseStatusCodeSame(200);

        // On simule une action sur le formulaire d'ajout
        $this->client->submitForm('submit', [
            'bookshelf[name]' => 'Testing',
            'bookshelf[description]' => 'Testing',
            'bookshelf[public]' => true,
        ]);

        $bookshelf = $this->repository->findOneBy(['name' => 'Testing']);

        // On vérifie que l'on redirige bien vers l'entité créée
        // et que la page renvoie bien un status code à 200
        self::assertResponseRedirects($this->router->generate('bks_bookshelf_view', ['ulid' => $bookshelf->getUlid()]));
        $this->client->request('GET', $this->router->generate('bks_bookshelf_view', ['ulid' => $bookshelf->getUlid()]));
        self::assertResponseStatusCodeSame(200);

        // On vérifie que le total d'entités a bien été incrémenté de 1
        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        // On efface l'entité créée
        $this->repository->remove($bookshelf, true);
    }

    public function testEdit(): void
    {
        // Création d'une entité de test que l'on insère dans notre base de test
        $fixture = new Bookshelf();
        $fixture->setName('My testEdit Title');
        $fixture->setDescription('My testEdit Description');
        $fixture->setPublic(true);
        $fixture->setOwner($this->user);

        $this->repository->save($fixture, true);

        // On identifie un user et on accède à la page d'édition
        $this->client->loginUser($this->user);

        $route = $this->router->generate('bks_bookshelf_edit', ['ulid' => $fixture->getUlid()]);

        $this->client->request('GET', $route);

        // On simule l'édition de l'entité créée
        $this->client->submitForm('submit', [
            'bookshelf[name]' => 'Something New',
            'bookshelf[description]' => 'Something New',
            'bookshelf[public]' => false,
        ]);

        // On vérifie que l'entité a bien été modifiée
        $fixture = $this->repository->findOneBy(['ulid' => $fixture->getUlid()]);

        self::assertSame('Something New', $fixture->getName());
        self::assertSame('Something New', $fixture->getDescription());
        self::assertSame(false, $fixture->isPublic());

        // On supprime l'entité créée
        $this->repository->remove($fixture, true);
    }

    public function testChangeOwnership(): void
    {
        // Création d'une entité de test que l'on insère dans notre base de test
        $fixture = new Bookshelf();
        $fixture->setName('My testChangeOwnership Title');
        $fixture->setDescription('My testChangeOwnership Description');
        $fixture->setPublic(true);
        $fixture->setOwner($this->user);

        $this->repository->save($fixture, true);

        // On identifie un user et on accède à la page d'édition
        $this->client->loginUser($this->user);

        $route = $this->router->generate('bks_bookshelf_edit_ownership', ['ulid' => $fixture->getUlid()]);

        $this->client->request('GET', $route);

        // On simule l'édition de l'entité créée
        $newUser = $this->userRepository->findOneBy(['username' => 'sophie']);

        $this->client->submitForm('submit', [
            'bookshelf_owner[owner]' => $newUser->getId(),
        ]);

        // On vérifie que l'entité a bien été modifiée
        $fixture = $this->repository->findOneBy(['ulid' => $fixture->getUlid()]);

        self::assertSame($newUser->getUsername(), $fixture->getOwner()->getUsername());
    }

    public function testDelete(): void
    {
        // Création d'une entité de test que l'on insère dans notre base de test
        $fixture = new Bookshelf();
        $fixture->setName('My testDelete Title');
        $fixture->setDescription('My testDelete Description');
        $fixture->setPublic(true);
        $fixture->setOwner($this->user);

        $this->repository->save($fixture, true);

        $count = count($this->repository->findAll());

        // On identifie un user et on accède à la page d'édition
        $this->client->loginUser($this->user);

        $route = $this->router->generate('bks_bookshelf_delete', ['ulid' => $fixture->getUlid()]);

        $this->client->request('GET', $route);


        // On simule la suppression de l'entité créée
        $this->client->submitForm('submit-delete');

        self::assertNull($this->repository->findOneBy(['name' => 'My testDelete Title']));
        self::assertEquals($count - 1, count($this->repository->findAll()));
    }
}