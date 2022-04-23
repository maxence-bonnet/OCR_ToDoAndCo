<?php

namespace Tests\App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HomeControllerTest extends WebTestCase
{
    const USER_1 = 'Carlotta';
    const ADMIN = 'Hubert';

    public static function setUpBeforeClass(): void
    {

    }

    /**
     * @dataProvider providePages
     */
    public function testHomePageIsAvailable(bool $authenticated, $pageName, $url, $expectedCode)
    {
        if ($authenticated) {
            $client = $this->getAuthenticatedClient();
        } else {
            $client = static::createClient();
        }
        $client->request('GET', $url);
        $response = $client->getResponse();
        $this->assertSame(
            $expectedCode,
            $response->getStatusCode(),
            sprintf(
                'Page "%s" should be return HTTP code : %s, but got HTTP code : "%s".',
                $pageName,
                $expectedCode,
                $response->getStatusCode()
            )
        );
    }

    public function providePages(): array
    {
        return [
            'home_anonymous' => [false, 'home_anonymous', '/', 302],
            'home_authenticated' => [true, 'home_authenticated', '/', 200],
        ];
    }

    /**
     * @dataProvider provideLinks
     */
    public function testHomePageLinksAreValid(string $link, string $url, string $selector, string $content)
    {
        $client = $this->getAuthenticatedClient();
        $crawler = $client->request('GET', '/');
        $linkSelected = $crawler->selectLink($link);
        
        $this->assertSame(
            $url,
            $linkSelected->attr('href'),
            sprintf('The "%s" link should take us to "%s" but has href="%s"', $link, $url, $linkSelected->attr('href'))
        ); 
        $crawler = $client->click($linkSelected->link());
        $result = $crawler->filter($selector)->text();
        $this->assertSame(
            $content,
            $result,
            sprintf('The element with selector "%s" should be available after visiting "%s"', $selector, $url)
        );
    }

    public function provideLinks()
    {
        return [
            'todo_tasks' => ['Voir les tâches à faire', '/taches/?tab=todo', '.nav-link.active.text-dark', 'Tâches à faire'],
            'done_tasks' => ['Voir les tâches terminées', '/taches/?tab=done', '.nav-link.active.text-dark', 'Tâches terminées'],
            'new_task' => ['Nouvelle tâche', '/taches/nouvelle', 'button[type=submit]', 'Enregister'],
        ];
    }

    public function getAuthenticatedClient(?string $username = null): KernelBrowser
    {
        if (null === $username) {
            $username = self::USER_1;
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
