<?php

namespace Tests\Repository;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserRespositoryTest extends WebTestCase
{
    public static int $fixturesUsersCount;
    
    public static function setUpBeforeClass(): void
    {
        self::$fixturesUsersCount = count(AppFixtures::getUsersData()) + 1; // +1 for admin added separately
    }

    public function testCountUsers()
    {
        $count = static::getContainer()->get(UserRepository::class)->count([]);
        $this->assertEquals(self::$fixturesUsersCount, $count);
    }

    public function testAddUser()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user =  (new User)->setUsername('new user')->setEmail('newemail@mail.fr')->setPassword('strongpassword');
        $userRepository->add($user);
        $count = $userRepository->count([]);
        $this->assertEquals(self::$fixturesUsersCount + 1, $count);
    }

    public function testRemoveUser()
    {
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy([]);
        $userRepository->remove($user);
        $count = $userRepository->count([]);
        $this->assertEquals(self::$fixturesUsersCount - 1, $count);
    }
}