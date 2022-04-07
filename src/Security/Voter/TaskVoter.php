<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class TaskVoter extends Voter
{
    public const EDIT = 'TASK_EDIT';
    public const DELETE = 'TASK_DELETE';

    public function __construct(private Security $security)
    {

    }

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]) && $subject instanceof Task;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }


        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var Task $subject */

        switch ($attribute) {
            case self::EDIT:
                return true; // :)
                break;
            case self::DELETE:
                return $this->canDelete($user, $subject);
                break;
        }

        return false;
    }

    private function canDelete(UserInterface $user, Task $task): bool
    {
        return $user === $task->getAuthor();
    }
}
