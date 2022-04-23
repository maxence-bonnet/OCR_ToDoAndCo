<?php

namespace Tests\Entity;

use App\Entity\Task;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskTest extends KernelTestCase
{
    public function testTaskHasError()
    {
        $task = $this->getEntity();
        $errors =  self::getContainer()->get('validator')->validate($task);
        $this->assertCount(2, $errors);
    }

    public function getEntity(): Task
    {
        return new Task();
    }
}
