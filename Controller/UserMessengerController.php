<?php

namespace GaylordP\UserMessengerBundle\Controller;

use App\Entity\User;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversation;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationMessage;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationUser;
use GaylordP\UserMessengerBundle\Form\UseMessengerConversationMessageType;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationMessageRepository;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationRepository;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationUserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserMessengerController extends AbstractController
{
    /**
     * @Route(
     *     {
     *         "fr": "/user/message",
     *     },
     *     name="user_message",
     *     methods=
     *     {
     *         "GET",
     *     }
     * )
     */
    public function index(
        UserMessengerConversationRepository $userMessengerConversationRepository,
        UserMessengerConversationMessageRepository $userMessengerConversationMessageRepository,
        UserMessengerConversationUserRepository $userMessengerConversationUserRepository
    ): Response {
        $conversations = $userMessengerConversationRepository->findAllByUser($this->getUser());

        if (!empty($conversations)) {
            $conversationsById = [];
            array_map(function($e) use(&$conversationsById) {
                $conversationsById[$e[0]->getId()] = $e;
            }, $conversations);

            $lastMessages = $userMessengerConversationMessageRepository->findById(array_column($conversations, 'last_message_id'));

            foreach ($lastMessages as $lastMessage) {
                $conversationsById[$lastMessage->getUserMessengerConversation()->getId()]['last_message'] = $lastMessage;
            }

            $conversationUsers = $userMessengerConversationUserRepository->findByUserMessengerConversation(array_keys($conversationsById));

            foreach ($conversationUsers as $conversationUser) {
                $conversationsById[$conversationUser->getUserMessengerConversation()->getId()]['users'][] = $conversationUser->getUser();
            }

            $conversations = $conversationsById;
        }

        return $this->render('@UserMessenger/index.html.twig', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/message/{slug}",
     *     },
     *     name="user_message_member",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Security("user.getId() !== member.getId()")
     */
    public function message(
        Request $request,
        User $member
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $conversation = $entityManager
            ->getRepository(UserMessengerConversation::class)
            ->findByTwoUsers(
                $this->getUser(),
                $member
            )
        ;

        if (null !== $conversation) {
            $conversationUsers = $entityManager
                ->getRepository(UserMessengerConversationUser::class)
                ->findByUserMessengerConversation($conversation, [
                    'id' => 'ASC',
                ])
            ;

            $messages = $entityManager
                ->getRepository(UserMessengerConversationMessage::class)
                ->findByUserMessengerConversationAndUser($this->getUser(), $conversation)
            ;

            /*
            $users[$conversation->getId()][$this->getUser()->getId()]->setReadAt(new \DateTime());
            */

            $entityManager->flush();
        }

        $message = new UserMessengerConversationMessage();
        $message->setUserMessengerConversation($conversation);

        $form = $this->createForm(UseMessengerConversationMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $conversation) {
                $conversation = new UserMessengerConversation();
                $conversation->setGroup(false);
                $message->setUserMessengerConversation($conversation);
                $entityManager->persist($conversation);

                $user1 = new UserMessengerConversationUser();
                $user1->setUser($this->getUser());
                $user1->setUserMessengerConversation($conversation);
                $entityManager->persist($user1);

                $user2 = new UserMessengerConversationUser();
                $user2->setUser($member);
                $user2->setUserMessengerConversation($conversation);
                $entityManager->persist($user2);
            }

            $entityManager->persist($message);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                [
                    'user.message.created_successfully',
                    [
                        '%username%' => $this->renderView('@User/button/_user.html.twig', [
                            'user' => $member,
                        ]),
                    ],
                    'user_messenger'
                ]
            );

            return $this->redirectToRoute('user_message_member', [
                'slug' => $member->getSlug(),
            ]);
        }

        return $this->render('@UserMessenger/message.html.twig', [
            'member' => $member,
            'conversation' => $conversation,
            'messages' => $messages ?? [],
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/message/{slug}/delete",
     *     },
     *     name="user_message_delete",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     * @Security("user.getId() !== member.getId()")
     */
    public function delete(
        Request $request,
        User $member
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $conversation = $entityManager
            ->getRepository(UserMessengerConversation::class)
            ->findByTwoUsers(
                $this->getUser(),
                $member
            )
        ;

        if (null === $conversation) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->render('@UserMessenger/delete.html.twig', [
                'member' => $member,
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $users = $entityManager
            ->getRepository(UserMessengerConversationUser::class)
            ->findByUserMessengerConversation($conversation)
        ;

        foreach ($users as $userMessage) {
            if ($userMessage->getUser() === $this->getUser()) {
                $userMessage->setDeletedBeforeBy($this->getUser());
                $userMessage->setDeletedBeforeAt(new \DateTime());
            }
        }

        $entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            [
                'user.message.deleted_successfully',
                [
                    '%username%' => $this->renderView('@User/button/_user.html.twig', [
                        'user' => $member,
                    ]),
                ],
                'user_messenger'
            ]
        );

        return $this->redirectToRoute('user_message');
    }
}
