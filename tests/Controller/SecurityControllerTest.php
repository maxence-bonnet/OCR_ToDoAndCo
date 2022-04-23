<?php

namespace Tests\App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerTest extends WebTestCase
{
    const USER = 'Carlotta';

    public function testRedirectIfAlreadyAuthenticated()
    {
        $client = $this->getAuthenticatedClient();
        $client->request('GET', '/connexion');
        $this->assertSame(
            Response::HTTP_FOUND,
            $client->getResponse()->getStatusCode(),
            'Authenticated user should redirected to homepage when trying to reach connexion page.'
        ); 
    }

    /**
     * @dataProvider getCredentials
     */
    public function testAuthentications($username, $password, $selector, $content)
    {
        $client = static::createClient();

        $client->request('GET', '/connexion');

        $client->submitForm('Connexion', [
            'username' => $username,
            'password' => $password,
        ]);

        $this->assertSame($client->getResponse()->getStatusCode(), Response::HTTP_FOUND);

        $crawler = $client->followRedirect();

        $this->assertSame(
            $crawler->filter($selector)->text(),
            $content,
            sprintf(
                'Login error with credentials ["%s", "%s"]',
                $username,
                $password,
            )
        );
    }

    public function getCredentials(): array
    {
        // username, password, expectedcode ?
        return [
            ['Robert', 'azerto', '.alert.alert-danger.alert-dismissible.fade.show', 'Les identifiants saisis sont invalides'], // unknown username
            ['Hubert', 'azerto', '.alert.alert-danger.alert-dismissible.fade.show', 'Les identifiants saisis sont invalides'], // known username, wrong password
            ['Hubert', 'azerty', 'h1', 'Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !'], // known username with password
        ];
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
}
