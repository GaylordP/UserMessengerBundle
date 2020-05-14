<?php

namespace GaylordP\UserMessengerBundle\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversation;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationMessage;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationUser;

class UserMessengerConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMessengerConversation::class);
    }

    public function findAllByUser(User $userLogged): array
    {
        return $this
            ->createQueryBuilder('conversation')
            ->innerJoin(UserMessengerConversationUser::class, 'messageUserLogged', 'WITH', '
                messageUserLogged.userMessengerConversation = conversation AND
                messageUserLogged.user = :userLogged
            ')
            ->innerJoin(UserMessengerConversationMessage::class, 'messageLast', 'WITH', '
                messageLast.userMessengerConversation = conversation AND
                (messageUserLogged.deletedBeforeAt IS NULL OR (messageLast.createdAt > messageUserLogged.deletedBeforeAt))
            ')
            ->leftJoin(UserMessengerConversationMessage::class, 'messageMax', 'WITH', '
                messageMax.userMessengerConversation = conversation AND
                (messageUserLogged.deletedBeforeAt IS NULL OR (messageMax.createdAt > messageUserLogged.deletedBeforeAt)) AND
                messageLast.id < messageMax.id
            ')
            ->andWhere('messageMax.id IS NULL')
            ->setParameter('userLogged', $userLogged)
            ->select('
                conversation,
                messageLast.id AS last_message_id
            ')
            ->orderBy('messageLast.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByTwoUsers(
        User $userLogged,
        User $userTo
    ): ?UserMessengerConversation {
        return $this
            ->createQueryBuilder('conversation')
            ->innerJoin(UserMessengerConversationUser::class, 'messageUserLogged', 'WITH', '
                messageUserLogged.userMessengerConversation = conversation AND
                messageUserLogged.user = :userLogged
            ')
            ->innerJoin(UserMessengerConversationUser::class, 'messageUserTo', 'WITH', '
                messageUserTo.userMessengerConversation = conversation AND
                messageUserLogged != messageUserTo AND
                messageUserTo.user = :userTo
            ')
            ->andWhere('conversation.group = :group')
            ->setParameter('group', false)
            ->setParameter('userLogged', $userLogged)
            ->setParameter('userTo', $userTo)
            ->select('
                conversation
            ')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
