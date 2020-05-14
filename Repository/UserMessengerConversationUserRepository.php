<?php

namespace GaylordP\UserMessengerBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationUser;

class UserMessengerConversationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMessengerConversationUser::class);
    }
}
