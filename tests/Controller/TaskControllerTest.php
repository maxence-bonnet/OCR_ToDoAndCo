<?php

namespace Tests\App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    const USER_1 = 'Carlotta';
    const USER_2 = 'Heinrich';
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

        if ($username) {
            $response = $client->getResponse();
            $this->assertSame(
                Response::HTTP_OK,
                $response->getStatusCode(),
                sprintf(
                    'The %s secure URL should be accessible for authenticated user, but got HTTP code : "%s".',
                    $pageName,
                    $response->getStatusCode()
                )
            );
        } else {
            $this->assertResponseRedirects(
                '/connexion',
                Response::HTTP_FOUND,
                sprintf('The %s secure URL should redirect anonymous users to the login form.', $pageName)
            );            
        }
    }

    public function testAddNewTask()
    {
        $client = $this->getAuthenticatedClient();

        $crawler = $client->request('GET', '/taches/nouvelle');

        $content = 'Pellentesque 123456789 et sapien pulvinar consectetur. Abnobas sunt hilotaes de placidus vita.';

        $client->submitForm('Enregister', [
            'task[title]' => 'Titre test',
            'task[content]' => $content,
        ]);

        $crawler = $client->followRedirect();

        $this->assertSame($content, $crawler->filter('#tasks_to_do')->filter('.task-card')->last()->filter('.card-text')->text());
    }

    public function testEditTask()
    {
        $client = $this->getAuthenticatedClient();
        $task = $this->findOneTask([]);

        $client->request('GET', "/taches/{$task->getId()}/modifier");
        $title = 'Ce titre a été modifié';
        $content = 'Ce contenu a été modifié';
        $client->submitForm('Modifier', [
            'task[title]' => $title,
            'task[content]' => $content,
        ]);
        $crawler = $client->followRedirect();

        $card = $crawler->filter("#task-{$task->getId()}");
        $newTitle = $card->filter("a")->text();
        $newContent = $card->filter(".card-text")->text();

        $this->assertSame($title, $newTitle);
        $this->assertSame($content, $newContent);
    }

    /**
     * @dataProvider getUsers
     */
    public function testOnlyAdminCanDeleteAnonymousTask($username)
    {
        $client = $this->getAuthenticatedClient($username);
        $task = $this->findOneTask(['author' => null]);
        $client->request('GET', "/taches/{$task->getId()}/modifier");
        $client->submitForm('Supprimer');

        if ($username === self::ADMIN) {
            $crawler = $client->followRedirect();
            $this->assertSame('La tâche a bien été supprimée !', $crawler->filter('.alert.alert-warning.alert-dismissible')->first()->text());
        } else {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $client->getResponse()->getStatusCode(),
                sprintf('Only granted ROLE_AMIN users should be able to delete an anonymous task.')
            ); 
        }
    }

    /**
     * @dataProvider getUsers
     */
    public function testTaskIsDeletableOnlyByOwnerOrAmin($username)
    {
        $owner = self::USER_1;
        $client = $this->getAuthenticatedClient($username);
        $user = $this->findOneUser(['username' => $owner]);
        $task = $this->findOneTask(['author' => $user->getId()]);
        $client->request('GET', "/taches/{$task->getId()}/modifier");
        $client->submitForm('Supprimer');

        if ($username === self::ADMIN || $owner === $username) {
            $crawler = $client->followRedirect();
            $this->assertSame('La tâche a bien été supprimée !', $crawler->filter('.alert.alert-warning.alert-dismissible')->first()->text());
        } else {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $client->getResponse()->getStatusCode(),
                'Tasks should be deletable by their owner (and admin) only.'
            ); 
        }
    }

    public function testTaskStatusIsToggled()
    {
        $client = $this->getAuthenticatedClient();
        $task = $this->findOneTask([]);

        $crawler = $client->request('GET', "/taches/");
        $button = 'Marquer comme faite';
        $alertResponse = 'faite !';
        if ($task->getIsDone()) {
            $button = 'Marquer non terminée';
            $alertResponse = 'à faire !';
        }

        $form = $crawler->filter("#toggle-form-task-{$task->getId()}")->selectButton($button)->form();
        $client->submit($form);
        $crawler = $client->followRedirect();

        $this->assertSame(
            "La tâche a été marquée : {$alertResponse}",
            $crawler->filter('.alert.alert-success.alert-dismissible')->text(),
            'Tasks are not toggled as expected.'
        );
    }

    public function getUrls(): array
    {
        $task = $this->findOneTask([]);
        self::ensureKernelShutdown();
        $id = $task->getId();
        return [
            'index' => [null, 'task_index', '/taches/'],
            'index' => [self::USER_1, 'task_index', '/taches/'],
            'new' => [null, 'task_new', '/taches/nouvelle'],
            'new' => [self::USER_1, 'task_new', '/taches/nouvelle'],
            'show' => [null, 'task_show', "/taches/{$id}"],
            'show' => [self::USER_1, 'task_show', "/taches/{$id}"],
            'edit' => [null, 'task_edit', "/taches/{$id}/modifier"],
            'edit' => [self::USER_1, 'task_edit', "/taches/{$id}/modifier"],
        ];
    }

    public function getUsers(): array
    {
        return [[self::USER_1], [self::USER_2], [self::ADMIN]];
    }

    public function findOneTask(array $criteria = []): ?Task
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        return $taskRepository->findOneBy($criteria);
    }

    public function findOneUser(array $criteria = []): ?User
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        return $userRepository->findOneBy($criteria);
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
