<?php

namespace GaylordP\UserMessengerBundle\Provider;

use GaylordP\UserMessengerBundle\Entity\UserMessengerConversation;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationMessageRepository;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationRepository;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationUserRepository;
use Symfony\Component\Security\Core\Security;

class UserMessengerProvider
{
    const CONVERSATION_USERS = '__users';
    const CONVERSATION_LAST_MESSAGE = '__lastMessage';

    protected $security;
    protected $userMessengerConversationMessageRepository;
    protected $userMessengerConversationUserRepository;

    public function __construct(
        Security $security,
        UserMessengerConversationMessageRepository $userMessengerConversationMessageRepository,
        UserMessengerConversationUserRepository $userMessengerConversationUserRepository
    ) {
        $this->security = $security;
        $this->userMessengerConversationMessageRepository = $userMessengerConversationMessageRepository;
        $this->userMessengerConversationUserRepository = $userMessengerConversationUserRepository;
    }

    public function addExtraInfos($conversation)
    {
        $listEntitiesById = [];

        if ($conversation instanceof UserMessengerConversation) {
            $listEntitiesById[$conversation->getId()] = $conversation;
        } elseif (is_array($conversation) && current($conversation) instanceof UserMessengerConversation) {
            array_map(function($e) use(&$listEntitiesById) {
                $listEntitiesById[$e->getId()] = $e;
            }, $conversation);
        }

        if (!empty($listEntitiesById)) {
            /*
             * Users
             */
            $conversationUsersIds = array_map(function($e) {
                if (false === property_exists($e, self::CONVERSATION_USERS)) {
                    return $e->getId();
                }
            }, $listEntitiesById);

            $conversationUsers = $this->userMessengerConversationUserRepository->findByUserMessengerConversation($conversationUsersIds);

            foreach ($conversationUsers as $conversationUser) {
                $listEntitiesById[$conversationUser->getUserMessengerConversation()->getId()]->{self::CONVERSATION_USERS}[] = $conversationUser;
            }

            /*
             * Last message
             */
            $conversationLastMessageIds = array_map(function($e) {
                if (false === property_exists($e, self::CONVERSATION_LAST_MESSAGE)) {
                    return $e->{UserMessengerConversationRepository::LAST_MESSAGE_ID};
                }
            }, $listEntitiesById);

            $lastMessages = $this->userMessengerConversationMessageRepository->findById($conversationLastMessageIds);

            foreach ($lastMessages as $lastMessage) {
                $listEntitiesById[$lastMessage->getUserMessengerConversation()->getId()]->{self::CONVERSATION_LAST_MESSAGE} = $lastMessage;
            }
        }

        return $conversation;
    }
}
