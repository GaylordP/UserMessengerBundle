<?php

namespace GaylordP\UserMessengerBundle\Controller;

use App\Entity\User;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversation;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationMessage;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationUser;
use GaylordP\UserMessengerBundle\Form\UserMessengerConversationMessageType;
use GaylordP\UserMessengerBundle\Provider\UserMessengerProvider;
use GaylordP\UserMessengerBundle\Repository\UserMessengerConversationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
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
        UserMessengerProvider $userMessengerProvider
    ): Response {
        $conversations = $userMessengerConversationRepository->findAllByUser($this->getUser());

        if (!empty($conversations)) {
            $userMessengerProvider->addExtraInfos($conversations);
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
        User $member,
        PublisherInterface $publisher
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
            $conversation->__users = $entityManager
                ->getRepository(UserMessengerConversationUser::class)
                ->findByUserMessengerConversation($conversation, [
                    'id' => 'ASC',
                ])
            ;

            $messages = $entityManager
                ->getRepository(UserMessengerConversationMessage::class)
                ->findByUserMessengerConversationAndUser($this->getUser(), $conversation)
            ;

            foreach ($conversation->__users as $conversationUser) {
                $conversationUser->setReadAt(new \DateTime());
            }

            $lastMessage = $messages[array_key_last($messages)];

            if ($lastMessage->getCreatedBy() === $this->getUser()) {
                foreach ($conversation->__users as $conversationUser) {
                    $update = new Update(
                        'https://bubble.lgbt/user/' . $conversationUser->getUser()->getSlug(),
                        json_encode([
                            'uuid' => $conversation->getUuid(),
                        ]),
                        true,
                        null,
                        'user_messenger_read'
                    );
                    $publisher($update);
                }
            }
        }

        $message = new UserMessengerConversationMessage();

        $form = $this->createForm(UserMessengerConversationMessageType::class, $message, [
            'attr' => [
                'action' => $request->getRequestUri(),
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $originalConversation = $conversation;

            if (null === $conversation) {
                $conversation = new UserMessengerConversation();
                $conversation->setUuid(uuid_create(UUID_TYPE_RANDOM));
                $conversation->setGroup(false);
                $entityManager->persist($conversation);

                $user1 = new UserMessengerConversationUser();
                $user1->setUser($this->getUser());
                $user1->setReadAt(new \DateTime());
                $user1->setUserMessengerConversation($conversation);
                $entityManager->persist($user1);

                $user2 = new UserMessengerConversationUser();
                $user2->setUser($member);
                $user2->setUserMessengerConversation($conversation);
                $entityManager->persist($user2);

                $conversation->__users = [
                    $user1,
                    $user2
                ];
            }

            $message->setUserMessengerConversation($conversation);

            $entityManager->persist($message);
            $entityManager->flush();

            if (null === $originalConversation && 2 === count($conversation->__users)) {
                $update = new Update(
                    'https://bubble.lgbt/user/' . $conversation->__users[0]->getUser()->getSlug(),
                    json_encode([
                        'tmpUuid' => $conversation->__users[0]->getUser()->getSlug() . '-' . $conversation->__users[1]->getUser()->getSlug(),
                        'uuid' => $conversation->getUuid(),
                        'delete_link' => $this->renderView('@UserMessenger/_delete_link.html.twig', [
                            'conversation' => $conversation,
                        ])
                    ]),
                    true,
                    null,
                    'user_messenger_replace_uuid'
                );
                $publisher($update);

                $update = new Update(
                    'https://bubble.lgbt/user/' . $conversation->__users[1]->getUser()->getSlug(),
                    json_encode([
                        'tmpUuid' => $conversation->__users[1]->getUser()->getSlug() . '-' . $conversation->__users[0]->getUser()->getSlug(),
                        'uuid' => $conversation->getUuid(),
                        'delete_link' => $this->renderView('@UserMessenger/_delete_link.html.twig', [
                            'conversation' => $conversation,
                        ])
                    ]),
                    true,
                    null,
                    'user_messenger_replace_uuid'
                );
                $publisher($update);
            }

            foreach ($conversation->__users as $conversationUser) {
                $update = new Update(
                    'https://bubble.lgbt/user/' . $conversationUser->getUser()->getSlug(),
                    json_encode([
                        'messageHtml' => $this->renderView('@UserMessenger/_message.html.twig', [
                            'page' => 'index',
                            'conversation' => $conversation,
                            'message' => $message,
                            'previous_date' => null,
                            'userPrintedThisMessage' => $conversationUser->getUser(),
                        ]),
                        'uuid' => $conversation->getUuid(),
                    ]),
                    true,
                    null,
                    'user_messenger_add_in_index_page'
                );
                $publisher($update);

                $update = new Update(
                    'https://bubble.lgbt/user/' . $conversationUser->getUser()->getSlug(),
                    json_encode([
                        'messageHtml' => $this->renderView('@UserMessenger/_message.html.twig', [
                            'page' => 'navbar',
                            'conversation' => $conversation,
                            'message' => $message,
                            'previous_date' => null,
                            'userPrintedThisMessage' => $conversationUser->getUser(),
                        ]),
                        'uuid' => $conversation->getUuid(),
                    ]),
                    true,
                    null,
                    'user_messenger_add_in_navbar'
                );
                $publisher($update);

                $update = new Update(
                    'https://bubble.lgbt/user/' . $conversationUser->getUser()->getSlug(),
                    json_encode([
                        'messageHtml' => $this->renderView('@UserMessenger/_message.html.twig', [
                            'page' => 'message',
                            'conversation' => $conversation,
                            'message' => $message,
                            'previous_date' => null,
                            'userPrintedThisMessage' => $conversationUser->getUser(),
                        ]),
                        'uuid' => $conversation->getUuid(),
                    ]),
                    true,
                    null,
                    'user_messenger_add_in_message_page'
                );
                $publisher($update);
            }

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'success',
                ], Response::HTTP_OK);
            } else {
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
        }

        if ($request->isXmlHttpRequest() && $form->isSubmitted()) {
            return new JsonResponse([
                'status' => 'form_error',
                'formHtml' => $this->renderView('@UserMessenger/_message_form.html.twig', [
                    'form' => $form->createView(),
                ]),
            ], Response::HTTP_OK);
        } else {
            return $this->render('@UserMessenger/message.html.twig', [
                'member' => $member,
                'conversation' => $conversation,
                'messages' => $messages ?? [],
                'form' => $form->createView(),
            ]);
        }
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/message/{uuid}/delete",
     *     },
     *     name="user_message_member_delete",
     *     methods=
     *     {
     *         "GET",
     *         "POST",
     *     }
     * )
     */
    public function delete(
        Request $request,
        UserMessengerConversation $conversation,
        PublisherInterface $publisher
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $users = $entityManager
            ->getRepository(UserMessengerConversationUser::class)
            ->findByUserMessengerConversation($conversation)
        ;

        $member = null;

        foreach ($users as $userMessage) {
            if ($userMessage->getUser() !== $this->getUser()) {
                $member = $userMessage->getUser();
            }
        }


        dump('contrôler que le member appartient bien à la conversation');

        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->render('@UserMessenger/delete.html.twig', [
                'conversation' => $conversation,
                'member' => $member,
            ]);
        }

        foreach ($users as $userMessage) {
            if ($userMessage->getUser() === $this->getUser()) {
                $userMessage->setDeletedBeforeBy($this->getUser());
                $userMessage->setDeletedBeforeAt(new \DateTime());
            }
        }

        $entityManager->flush();

        $update = new Update(
            'https://bubble.lgbt/user/' . $this->getUser()->getSlug(),
            json_encode([
                'uuid' => $conversation->getUuid(),
            ]),
            true,
            null,
            'user_messenger_delete'
        );
        $publisher($update);

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
