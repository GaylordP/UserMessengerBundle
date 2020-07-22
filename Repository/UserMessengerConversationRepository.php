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
    const LAST_MESSAGE_ID = '__lastMessageId';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMessengerConversation::class);
    }

    public function findAllByUser(User $userLogged, int $limit = null): array
    {
        $qb = $this
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
        ;

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $results = $qb
            ->getQuery()
            ->getResult()
        ;

        $return = [];

        foreach ($results as $result) {
            $result[0]->{self::LAST_MESSAGE_ID} = $result['last_message_id'];

            $return[] = $result[0];
        }

        return $return;
    }

    public function countUnread(User $userLogged): array
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
            ->andWhere('messageLast.createdBy != :userLogged')
            ->andWhere('messageUserLogged.readAt < messageLast.createdAt')
            ->setParameter('userLogged', $userLogged)
            ->select('
                conversation
            ')
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
