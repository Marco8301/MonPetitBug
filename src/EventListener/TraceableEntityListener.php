<?php

namespace App\EventListener;

use App\Entity\Bug;
use App\Entity\Comment;
use App\Traits\UserAwareTrait;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class TraceableEntityListener
{
    use UserAwareTrait;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /* @phpstan-ignore-next-line */
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Comment && !$entity instanceof Bug) {
            return;
        }

        $entity->setUpdatedAt();
    }

    /* @phpstan-ignore-next-line */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Comment && !$entity instanceof Bug) {
            return;
        }

        $entity->setCreatedAt();
        $entity->setUser($this->getUser());
    }
}