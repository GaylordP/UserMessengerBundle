<?php

namespace GaylordP\UserMessengerBundle\Twig;

use GaylordP\UserMessengerBundle\Provider\UserMessengerProvider;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationRepository;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
    private $security;
    private $twig;
    private $userMessengerProvider;
    private $userMessengerConversationRepository;

    private $countUserMessageUnread = null;
    private $userMessenger = null;

    public function __construct(
        Security $security,
        Environment $twig,
        UserMessengerProvider $userMessengerProvider,
        UserMessengerConversationRepository $userMessengerConversationRepository
    ) {
        $this->security = $security;
        $this->twig = $twig;
        $this->userMessengerProvider = $userMessengerProvider;
        $this->userMessengerConversationRepository = $userMessengerConversationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'user_messenger',
                [$this, 'getUserMessenger'],
            ),
            new TwigFunction(
                'count_user_message_unread',
                [$this, 'countUserMessageUnread'],
            ),
        ];
    }

    public function getUserMessenger(): array
    {
        if (null === $this->userMessenger) {
            $this->userMessenger = $this
                ->userMessengerConversationRepository
                ->findAllByUser($this->security->getUser())
            ;

            dump($this->userMessengerProvider->addExtraInfos($this->userMessenger));
        }

        return $this->userMessenger;
    }

    public function countUserMessageUnread(): int
    {
        if (null === $this->countUserMessageUnread) {
            $this->countUserMessageUnread = 0;
        }

        return $this->countUserMessageUnread;
    }
}
