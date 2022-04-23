<?php

namespace Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskUnitTest extends TestCase
{
    const TITLE = 'title';
    const CONTENT = 'th!s !s @ t3st c0nt3nt ;';

    private ?User $author;
    private ?User $editor;
    private \DateTimeImmutable $date;
    
    public function setUp(): void
    {
        $this->author = new User();
        $this->editor = new User();
        $this->date = new \DateTimeImmutable();
    }

    public function testTaskHasInitialDatas()
    {
        $task = new Task();
        $this->assertTrue($task->getCreatedAt() instanceof \DateTimeImmutable);
        $this->assertTrue($task->getUpdatedAt() instanceof \DateTimeImmutable);
        $this->assertTrue($task->getIsDone() === false);
    }

    public function testIsTrue()
    {
        $task = $this->getEntity();
        $this->assertSame($task->getTitle(), self::TITLE);
        $this->assertSame($task->getContent(), self::CONTENT);
        $this->assertSame($task->getIsDone(), true);
        $this->assertSame($task->getAuthor(), $this->author);
        $this->assertSame($task->getUpdatedBy(), $this->editor);
        $this->assertSame($task->getCreatedAt(), $this->date);
    }

    public function testIsFalse()
    {
        $task = $this->getEntity();
        $this->assertFalse($task->getTitle() === 'another title');
        $this->assertFalse($task->getContent() === 'th!s !s @n0th3r t3st c0nt3nt ;');
        $this->assertFalse($task->getIsDone() === false);
        $this->assertFalse($task->getAuthor() === $this->editor);
        $this->assertFalse($task->getUpdatedBy() === $this->author);
        $this->assertFalse($task->getCreatedAt() === $this->date->modify('+1 day'));
    }

    public function testIsNull()
    {
        $task = new Task();
        $this->assertEmpty($task->getTitle());
        $this->assertEmpty($task->getContent());
        $this->assertEmpty($task->getAuthor());
        $this->assertEmpty($task->getUpdatedBy());
    }

    public function getEntity(): Task
    {
        $task = new Task();
        return $task
            ->setTitle(self::TITLE)
            ->setContent(self::CONTENT)
            ->setIsDone(true)
            ->setAuthor($this->author)
            ->setUpdatedBy($this->editor)
            ->setCreatedAt($this->date)
            ;
    }
}
