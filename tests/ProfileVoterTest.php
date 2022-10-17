<?php

namespace App\Tests;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileVoterTest extends WebTestCase
{
    public function testUnauthenticatedPublicProfile(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'brice']);

        $client->request('GET', '/profile/' . $testUser->getUsername());

        $this->assertResponseIsSuccessful();
    }

    public function testUnauthenticatedPrivateProfile(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'sophie']);

        $client->request('GET', '/profile/' . $testUser->getUsername());

        $loginRoute = static::getContainer()->get('router')->generate('bks_login', array(), false);

        $this->assertResponseRedirects($loginRoute);
    }

    public function testAuthenticatedOwnProfile(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'sophie']);

        $client->loginUser($testUser);

        $client->request('GET', '/profile/' . $testUser->getUsername());
        $this->assertResponseIsSuccessful();
    }

    public function testAuthenticatedPrivateProfileAnotherUser(): void
    {
        $client = static::createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        /** @var User $privateUser */
        $privateUser = $userRepository->findOneBy(['username' => 'sophie']);
        /** @var User $testUser */
        $testUser = $userRepository->findOneBy(['username' => 'brice']);

        $client->loginUser($testUser);

        $client->request('GET', '/profile/' . $privateUser->getUsername());

        $this->assertResponseStatusCodeSame(403);
    }
}
