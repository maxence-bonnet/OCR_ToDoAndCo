<?php

namespace Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserHasAtLeastRoleUser()
    {
        $user = new User();
        $this->assertTrue(in_array('ROLE_USER', $user->getRoles()));
    }

    public function testUserIdentifierIsUsername()
    {
        $user = (new User())->setUsername('username');
        $this->assertSame($user->getUserIdentifier(), $user->getUsername());
    }
}
