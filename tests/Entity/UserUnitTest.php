<?php

namespace Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class UserUnitTest extends TestCase
{
    const USERNAME = 'username';
    const ROLES = ['ROLE_USER', 'ROLE_EXAMPLE'];
    const EMAIL = 'email@email.cz';
    const PASSWORD = 'strongAndHashed';

    private \DateTimeImmutable $date;

    public function setUp(): void
    {
        $this->date = new \DateTimeImmutable();
    }
    
    public function testUserHasInitialDatas()
    {
        $user = new User();
        $this->assertTrue($user->getCreatedAt() instanceof \DateTimeImmutable);
        $this->assertTrue($user->getTasks() instanceof ArrayCollection && $user->getTasks()->count() === 0);
        $this->assertTrue($user->getUpdatedTasks() instanceof ArrayCollection && $user->getUpdatedTasks()->count() === 0);
    }

    public function testIsTrue()
    {     
        $user = $this->getEntity();
        $this->assertSame($user->getUsername(), self::USERNAME);
        $this->assertSame($user->getRoles(), self::ROLES);
        $this->assertSame($user->getEmail(), self::EMAIL);
        $this->assertSame($user->getPassword(), self::PASSWORD);
        $this->assertSame($user->getCreatedAt(), $this->date);
    }

    public function testIsFalse()
    {
        $user = $this->getEntity();
        $this->assertFalse($user->getUsername() === 'false');
        $this->assertFalse($user->getRoles() === ['ROLE_USER']);
        $this->assertFalse($user->getEmail() === '');
        $this->assertFalse($user->getPassword() === '');
        $this->assertFalse($user->getCreatedAt() === $this->date->modify('+1 day'));
    }

    public function testIsNull()
    {
        $user = new User();
        $this->assertEmpty($user->getUsername());
        $this->assertEmpty($user->getEmail());
    }

    public function testUserTaskAssociationIsWorking()
    {
        $user = new User();

        $task1 = new Task();
        $user->addTask($task1);
        $rand1 = rand(0, 5);
        for ($i = 0; $i < $rand1; $i++) {
            $user->addTask(new Task());
        }

        $task2 = new Task();
        $user->addUpdatedTask($task2);
        $rand2 = rand(0, 5);
        for ($i = 0; $i < $rand2; $i++) {
            $user->addUpdatedTask(new Task());
        }

        $countTaskWriten = $user->getTasks()->count();
        $this->assertEquals($rand1 + 1, $countTaskWriten, 'User Task association failed after first count');

        $countTaskEdited = $user->getUpdatedTasks()->count();
        $this->assertEquals($rand2 + 1, $countTaskEdited, 'User UpdatedTask association failed after first count');

        $user->removeTask($task1);
        $countTaskWriten = $user->getTasks()->count();
        $this->assertEquals($rand1, $countTaskWriten, 'User Task association failed after remove');

        $user->removeUpdatedTask($task2);
        $countTaskEdited = $user->getUpdatedTasks()->count();
        $this->assertEquals($rand2, $countTaskEdited, 'User UpdatedTask association failed after remove');
    }

    public function getEntity(): User
    {
        $user = new User();
        return $user
            ->setUsername(self::USERNAME)
            ->setRoles(self::ROLES)
            ->setEmail(self::EMAIL)
            ->setPassword(self::PASSWORD)
            ->setCreatedAt($this->date)
            ;
    }
}
