<?php

namespace Tests\App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    const USER = 'Carlotta';

    public function testRedirectIfAlreadyAuthenticated()
    {
        $client = $this->getAuthenticatedClient();
        $client->request('GET', '/inscription');
        $this->assertSame(
            Response::HTTP_FOUND,
            $client->getResponse()->getStatusCode(),
            'Authenticated user should redirected to homepage when trying to reach registration page.'
        ); 
    }

    public function testFormValidation()
    {
        $usernameInput = 'registration_form[username]';
        $emailInput = 'registration_form[email]';
        $plainPasswordInput = 'registration_form[plainPassword][first]';

        $client = static::createClient();
        $client->request('GET', '/inscription');
        $crawler = $client->submitForm('Inscription', [
            $usernameInput => 'Hubert',
            $emailInput => 'admin@monmail.fr',
            $plainPasswordInput => 'azerty',
            'registration_form[plainPassword][second]' => 'azerto',
        ]);
        $this->assertEquals(3, $crawler->filter('.invalid-feedback')->count());
        $this->assertSame('form-control is-invalid', $crawler->filter("input[name=\"{$usernameInput}\"]")->attr('class'), 'Username input should return a validation error.');
        $this->assertSame('form-control is-invalid', $crawler->filter("input[name=\"{$emailInput}\"]")->attr('class'), 'Email input should return a validation error.');
        $this->assertSame('form-control is-invalid', $crawler->filter("input[name=\"{$plainPasswordInput}\"]")->attr('class'), 'Password input should return a validation error.');
    }

    public function testRegistrationIsSuccessful()
    {
        $client = static::createClient();
        $usersCount1 = static::getContainer()->get(UserRepository::class)->count([]);
        $client->request('GET', '/inscription');
        $client->submitForm('Inscription', [
            'registration_form[username]' => 'Hubert2',
            'registration_form[email]'=> 'admin2@monmail.fr',
            'registration_form[plainPassword][first]' => 'azerty',
            'registration_form[plainPassword][second]' => 'azerty',
        ]);
        $this->assertResponseRedirects('/connexion', 302);
        $crawler = $client->followRedirect();
        $this->assertSame($crawler->filter('.alert.alert-success.alert-dismissible')->text(), 'Félicitations, votre inscription est validée, vous pouvez maintenant vous connecter', 'Registration failed.');
        $usersCount2 = static::getContainer()->get(UserRepository::class)->count([]);
        $this->assertEquals($usersCount1 + 1, $usersCount2, 'User was not persisted after successful registration.');
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
