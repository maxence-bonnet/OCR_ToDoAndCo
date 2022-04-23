<?php

namespace Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskRespositoryTest extends WebTestCase
{
    public static int $fixturesTasksCount;
    
    public static function setUpBeforeClass(): void
    {
        self::$fixturesTasksCount = count(AppFixtures::getTasksData());
    }

    public function testCountTasks()
    {
        $count = static::getContainer()->get(TaskRepository::class)->count([]);
        $this->assertEquals(self::$fixturesTasksCount, $count);
    }

    public function testFindAllJoinUser()
    {
        /** @var TaskRepository $taskRepository  */
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $count = count($taskRepository->findAllJoinUser());
        $this->assertEquals(self::$fixturesTasksCount, $count, sprintf('Method TaskRepository::findAllJoinUser() does not seem to recover all expected tasks.'));
    }

    public function testAddTask()
    {
        $task =  (new Task)->setTitle('new task')->setContent('new content');
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $taskRepository->add($task);
        $count = $taskRepository->count([]);
        $this->assertEquals(self::$fixturesTasksCount + 1, $count);
        return $count;
    }


    public function testRemoveTask()
    {
        $taskRepository = static::getContainer()->get(TaskRepository::class);
        $task = $taskRepository->findOneBy([]);
        $taskRepository->remove($task);
        $count = $taskRepository->count([]);
        $this->assertEquals(self::$fixturesTasksCount - 1, $count);
    }
}