<?php

namespace GaylordP\UserMessengerBundle\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversation;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationMessage;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationUser;

class UserMessengerConversationMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMessengerConversationMessage::class);
    }

    public function findByUserMessengerConversationAndUser(User $userLogged, UserMessengerConversation $conversation): array
    {
        return $this
            ->createQueryBuilder('message')
            ->innerJoin(UserMessengerConversationUser::class, 'messageUserLogged', 'WITH', '
                messageUserLogged.userMessengerConversation = message.userMessengerConversation AND
                messageUserLogged.user = :userLogged
            ')
            ->andWhere('message.userMessengerConversation = :conversation')
            ->andWhere('(messageUserLogged.deletedBeforeAt IS NULL OR (message.createdAt > messageUserLogged.deletedBeforeAt))')
            ->andWhere('(messageUserLogged.deletedBeforeAt IS NULL OR (message.createdAt > messageUserLogged.deletedBeforeAt))')
            ->setParameter('conversation', $conversation)
            ->setParameter('userLogged', $userLogged)
            ->select('
                message
            ')
            ->orderBy('message.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
