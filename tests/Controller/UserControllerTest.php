<?php

namespace Tests\App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    const USER = 'Carlotta';
    const ADMIN = 'Hubert';

    /**
     * @dataProvider getUrls
     */
    public function testUserAccess(?string $username, $pageName, $url)
    {
        $client = static::createClient();

        if ($username) {
            $client = $this->login($client, $username);
        }

        $client->request('GET', $url);

        if ($username && $username === self::ADMIN) {
            $response = $client->getResponse();
            $this->assertSame(
                Response::HTTP_OK,
                $response->getStatusCode(),
                sprintf(
                    'The %s secure URL should be accessible for authenticated admin, but got HTTP code : "%s".',
                    $pageName,
                    $response->getStatusCode()
                )
            );
        } elseif ($username && $username === self::USER) {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $client->getResponse()->getStatusCode(),
                'Users management should be accessible by admin only.'
            );
        } else {
            $this->assertResponseRedirects(
                '/connexion',
                Response::HTTP_FOUND,
                sprintf('The %s secure URL should redirect anonymous users to the login form.', $pageName)
            );            
        }
    }

    public function getUrls(): array
    {
        $user = $this->findOneUser([]);
        self::ensureKernelShutdown();
        $id = $user->getId();
        return [
            'index_anonymous' => [null, 'user_index', '/utilisateurs/'],
            'index_role_user' => [self::USER, 'user_index', '/utilisateurs/'],
            'index_role_admin' => [self::ADMIN, 'user_index', '/utilisateurs/'],
            'new_anonymous' => [null, 'user_new', '/utilisateurs/nouveau'],
            'new_role_user' => [self::USER, 'user_new', '/utilisateurs/nouveau'],
            'new_role_admin' => [self::ADMIN, 'user_new', '/utilisateurs/nouveau'],
            'show_anonymous' => [null, 'user_show', "/utilisateurs/{$id}"],
            'show_role_user' => [self::USER, 'user_show', "/utilisateurs/{$id}"],
            'show_role_admin' => [self::ADMIN, 'user_show', "/utilisateurs/{$id}"],
            'edit_anonymous' => [null, 'user_edit', "/utilisateurs/{$id}/modifier"],
            'edit_role_user' => [self::USER, 'user_edit', "/utilisateurs/{$id}/modifier"],
            'edit_role_admin' => [self::ADMIN, 'user_edit', "/utilisateurs/{$id}/modifier"],
        ];
    }

    public function testNewUserValidation()
    {
        $usernameInput = 'user[username]';
        $emailInput = 'user[email]';
        $plainPasswordInput = 'user[plainPassword][first]';

        $client = $this->getAuthenticatedClient(self::ADMIN);

        $client->request('GET', '/utilisateurs/nouveau');
        $crawler = $client->submitForm('Enregistrer', [
            $usernameInput => 'Hubert',
            $emailInput => 'admin@monmail.fr',
            $plainPasswordInput => 'azerty',
            'user[plainPassword][second]' => 'azerto',
        ]);
        $this->assertEquals(3, $crawler->filter('.invalid-feedback')->count());
        $this->assertSame('form-control is-invalid', $crawler->filter("input[name=\"{$usernameInput}\"]")->attr('class'), 'Username input should return a validation error.');
        $this->assertSame('form-control is-invalid', $crawler->filter("input[name=\"{$emailInput}\"]")->attr('class'), 'Email input should return a validation error.');
        $this->assertSame('form-control is-invalid', $crawler->filter("input[name=\"{$plainPasswordInput}\"]")->attr('class'), 'Password input should return a validation error.');
    }

    public function testNewUserCreationSuccessful()
    {
        $username = 'Hubert2';
        $client = $this->getAuthenticatedClient(self::ADMIN);
        $usersCount1 = static::getContainer()->get(UserRepository::class)->count([]);
        $client->request('GET', '/utilisateurs/nouveau');
        $client->submitForm('Enregistrer', [
            'user[username]' => $username,
            'user[email]'=> 'admin2@monmail.fr',
            'user[roles]'=> 'ROLE_ADMIN',
            'user[plainPassword][first]' => 'azerty',
            'user[plainPassword][second]' => 'azerty',
        ]);
        $this->assertResponseRedirects('/utilisateurs/', Response::HTTP_SEE_OTHER);
        $crawler = $client->followRedirect();
        $this->assertSame($crawler->filter('.alert.alert-success.alert-dismissible')->text(), "L'utilisateur {$username} a bien été créé", 'New user creation failed.');
        $usersCount2 = static::getContainer()->get(UserRepository::class)->count([]);
        $this->assertEquals($usersCount1 + 1, $usersCount2, 'User was not persisted after successful creation.');
    }

    public function testUserDelete()
    {
        $client = $this->getAuthenticatedClient(self::ADMIN);
        $user = $this->findOneUser(['username' => 'Heinrich']);
        $usersCount1 = static::getContainer()->get(UserRepository::class)->count([]);
        $client->request('GET', "/utilisateurs/{$user->getId()}/modifier");
        $client->submitForm('Supprimer');
        $crawler = $client->followRedirect();
        $this->assertSame("L'utilisateur {$user->getUsername()} a bien été supprimé.", $crawler->filter('.alert.alert-warning.alert-dismissible')->text());
        $usersCount2 = static::getContainer()->get(UserRepository::class)->count([]);
        $this->assertEquals($usersCount1 - 1, $usersCount2, 'User was not persisted after deletion.');
    }


    public function testUserUpdate()
    {
        $client = $this->getAuthenticatedClient(self::ADMIN);

        $user = $this->findOneUser(['username' => self::USER]);
        $this->assertFalse(in_array('ROLE_ADMIN', $user->getRoles()));

        $client->request('GET', "/utilisateurs/{$user->getId()}/modifier");
        $client->submitForm('Modifier', [
            'user[roles]'=> 'ROLE_ADMIN',
        ]);

        $user = $this->findOneUser(['username' => self::USER]);
        $this->assertTrue(in_array('ROLE_ADMIN', $user->getRoles()));
    }

    public function testPasswordIsNotRequiredWhenEditingUser()
    {
        $client = $this->getAuthenticatedClient(self::ADMIN);
        $user = $this->findOneUser(['username' => self::USER]);
        $crawler = $client->request('GET', "/utilisateurs/{$user->getId()}/modifier");
        $this->assertNull($crawler->filter("input[name=\"user[plainPassword][first]\"]")->attr('required'));
    }

    public function testPasswordIsRequiredWhenCreatingUser()
    {
        $client = $this->getAuthenticatedClient(self::ADMIN);
        $crawler = $client->request('GET', "/utilisateurs/nouveau");
        $this->assertEquals("required", $crawler->filter("input[name=\"user[plainPassword][first]\"]")->attr('required'), 'Password input should be required when creating user.');
    }

    public function getAuthenticatedClient(?string $username = null): KernelBrowser
    {
        if (null === $username) {
            $username = self::USER;
        }
        $client = static::createClient();
        return $this->login($client, $username);
    }
    
    public function login(KernelBrowser $client, ?string $username): KernelBrowser
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['username' => $username]);
        return $client->loginUser($user);
    }

    public function findOneUser(array $criteria = []): ?User
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        return $userRepository->findOneBy($criteria);
    }
}
