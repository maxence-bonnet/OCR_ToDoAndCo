<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use function Symfony\Component\String\u;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadTasks($manager);
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $admin = $this->getAdminUser();
        $manager->persist($admin);
        foreach ($this->getUsersData() as [$reference, $username, $email]) {
            $user = (new User())
                ->setUsername($username)
                ->setRoles(['ROLE_USER'])
                ->setPassword($admin->getPassword()) // for performance reasons, the password is the same for everyone
                ->setEmail($email);
            $manager->persist($user);
            $this->addReference('usr-'.$reference, $user);
        }
        $manager->flush();
    }

    private function getAdminUser(): User
    {
        $admin = (new User())
            ->setUsername('Hubert')
            ->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@monmail.fr');
        $fixedPassword = $this->passwordHasher->hashPassword($admin, 'azerty');
        $admin->setPassword($fixedPassword);
        $this->addReference('usr-1', $admin);
        return $admin;
    }

    public static function getUsersData(): array
    {
        return [
            // $userData = [$reference, $username, $email];
            ['2', 'Armand', 'armand@monmail.fr'],
            ['3', 'Dolores', 'dolores@monmail.fr'],
            ['4', 'Carlotta', 'carlotta@monmail.fr'],
            ['5', 'Heinrich', 'heinrich@monmail.fr'],
        ];
    }

    private function loadTasks(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable();
        foreach ($this->getTasksData() as [$ownerReference, $title]) {
            $updated = rand(0, 1);
            $task = (new Task())
                ->setTitle($title)
                ->setContent($this->getRandomText(random_int(64, 128)))
                ->setIsDone(rand(0, 1));
            if ($ownerReference) {
                $task->setAuthor($this->getReference('usr-'.$ownerReference));
                if ($updated) {
                    $randomUser = rand(1, (count($this->getUsersData()) - 1));
                    $randomMinutes = rand(1, 5);
                    $task
                        ->setUpdatedBy($this->getReference('usr-'.$randomUser))
                        ->setUpdatedAt($now->modify("+ {$randomMinutes} minutes"));
                }
            }
            $manager->persist($task);
        }
        $manager->flush();
    }

    public static function getTasksData(): array
    {
        return [
            // $taskData = [$ownerReference, $title];
            ['1', 'Trouver Von Zimmel'],
            ['1', 'Recupérer le micro-film'],
            ['1', 'Ne jamais céder devant la barbarie'],
            ['1', 'Fabriquer un pédalo conforme aux plans'],
            ['2', 'Superviser les opération'],
            ['2', 'Rire aux blagues d\'Hubert'],
            ['3', 'Mener l\'enquête'],
            ['3', 'Retrouver Heinrich'],
            ['3', 'Faire changer les mentalités'],
            ['4', 'Charmer Hubert'],
            ['4', 'Tendre un piège à Hubert'],
            ['5', 'Infiltrer un groupe lambda'],
            ['5', 'Gagner la confiance d\'Hubert'],
            ['5', 'Tendre un piège à Hubert'],
            [null, 'Tâche anonyme 1'],
            [null, 'Tâche anonyme 2'],
            [null, 'Tâche anonyme 3'],
            [null, 'Tâche anonyme 4'],
        ];
    }

    private function getRandomText(int $maxLength = 255): string
    {
        $phrases = $this->getPhrases();
        shuffle($phrases);

        do {
            $text = u('. ')->join($phrases)->append('!');
            array_pop($phrases);
        } while ($text->length() > $maxLength);

        return $text;
    }

    private function getPhrases(): array
    {
        return [
            'Lorem ipsum dolor sit amet consectetur adipiscing elit',
            'Pellentesque vitae velit ex',
            'Mauris dapibus risus quis suscipit vulputate',
            'Eros diam egestas libero eu vulputate risus',
            'In hac habitasse platea dictumst',
            'Morbi tempus commodo mattis',
            'Ut suscipit posuere justo at vulputate',
            'Ut eleifend mauris et risus ultrices egestas',
            'Aliquam sodales odio id eleifend tristique',
            'Urna nisl sollicitudin id varius orci quam id turpis',
            'Nulla porta lobortis ligula vel egestas',
            'Curabitur aliquam euismod dolor non ornare',
            'Sed varius a risus eget aliquam',
            'Nunc viverra elit ac laoreet suscipit',
            'Pellentesque et sapien pulvinar consectetur',
            'Ubi est barbatus nix',
            'Abnobas sunt hilotaes de placidus vita',
            'Ubi est audax amicitia',
            'Eposs sunt solems de superbus fortis',
            'Vae humani generis',
            'Diatrias tolerare tanquam noster caesium',
            'Teres talis saepe tractare de camerarius flavum sensorem',
            'Silva de secundus galatae demitto quadra',
            'Sunt accentores vitare salvus flavum parses',
            'Potus sensim ad ferox abnoba',
            'Sunt seculaes transferre talis camerarius fluctuies',
            'Era brevis ratione est',
            'Sunt torquises imitari velox mirabilis medicinaes',
            'Mineralis persuadere omnes finises desiderium',
            'Bassus fatalis classiss virtualiter transferre de flavum',
            'Dictum non consectetur',
        ];
    }
}
