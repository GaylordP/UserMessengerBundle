<?php

namespace GaylordP\UserMessengerBundle\Controller;

use App\Entity\User;
use App\Entity\UserMedia;
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
use Symfony\Contracts\Translation\TranslatorInterface;

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
        TranslatorInterface$translator,
        PublisherInterface $publisher,
        UserMessengerProvider $userMessengerProvider
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

            $conversation->__lastMessage = !empty($messages) ? $messages[array_key_last($messages)] : null;

            $this->read($conversation, $publisher, $userMessengerProvider);
        }

        $message = new UserMessengerConversationMessage();

        $form = $this->createForm(UserMessengerConversationMessageType::class, $message, [
            'attr' => [
                'action' => $request->getRequestUri(),
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ('upload' === $this->get('request_stack')->getMasterRequest()->get('_route')) {
                if ($form->get('upload')->isValid()) {
                    $userMedia = new UserMedia();
                    $userMedia->setMedia($form->get('upload')->getData());

                    $entityManager->persist($userMedia);
                    $entityManager->flush();

                    $newForm = $this->createForm(UserMessengerConversationMessageType::class, $message);

                    return new JsonResponse([
                        'formHtml' => $this->renderView('@UserMessenger/_message_form.html.twig', [
                            'form' => $newForm->createView(),
                        ]),
                    ], Response::HTTP_OK);
                } else {
                    $messageList = [];

                    foreach ($form->get('upload')->getErrors(true) as $error) {
                        $messageList[] = $translator->trans($error->getOrigin()->getConfig()->getOption('label'), [], $error->getOrigin()->getConfig()->getOption('translation_domain')) . ' : ' . $error->getMessage();
                    }

                    return new JsonResponse(implode(' ; ', $messageList), Response::HTTP_BAD_REQUEST);
                }
            }

            if ($form->isValid()) {
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
                            'sender_or_recipient' => $this->getUser() === $conversationUser->getUser() ? 'sender' : 'recipient',
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
            } elseif($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'status' => 'form_error',
                    'formHtml' => $this->renderView('@UserMessenger/_message_form.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ], Response::HTTP_OK);
            }
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
     *         "fr": "/user/message/{uuid}/read",
     *     },
     *     name="user_message_read",
     *     methods=
     *     {
     *         "GET",
     *     },
     *     condition="true === request.isXmlHttpRequest()"
     * )
     */
    public function read(
        UserMessengerConversation $conversation,
        PublisherInterface $publisher,
        UserMessengerProvider $userMessengerProvider
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $users = $entityManager
            ->getRepository(UserMessengerConversationUser::class)
            ->findByUserMessengerConversation($conversation)
        ;

        $userInConversation = false;

        foreach ($users as $userMessage) {
            if ($userMessage->getUser() === $this->getUser()) {
                $userInConversation = true;
            }
        }

        if (false === $userInConversation) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $userMessengerProvider->addExtraInfos($conversation);

        foreach ($conversation->__users as $conversationUser) {
            if ($conversationUser->getUser() === $this->getUser()) {
                if (
                    null !== $conversation->__lastMessage
                        &&
                    2 === count($conversation->__users)
                        &&
                    $conversation->__lastMessage->getCreatedBy() !== $this->getUser()
                        &&
                    $conversation->__lastMessage->getCreatedAt() > $conversationUser->getReadAt()
                ) {
                    foreach ($conversation->__users as $conversationUserMercure) {
                        $update = new Update(
                            'https://bubble.lgbt/user/' . $conversationUserMercure->getUser()->getSlug(),
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

                $conversationUser->setReadAt(new \DateTime());

                $entityManager->flush();
            }
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    /**
     * @Route(
     *     {
     *         "fr": "/user/message/{uuid}/delete",
     *     },
     *     name="user_message_delete",
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
        UserMessengerProvider $userMessengerProvider,
        PublisherInterface $publisher
    ): Response {
        $entityManager = $this->getDoctrine()->getManager();

        $users = $entityManager
            ->getRepository(UserMessengerConversationUser::class)
            ->findByUserMessengerConversation($conversation)
        ;
        $countUsers = count($users);

        $userInConversation = false;
        $member = null;

        foreach ($users as $userMessage) {
            if ($userMessage->getUser() === $this->getUser()) {
                $userInConversation = true;
            }

            if (2 === $countUsers && $userMessage->getUser() !== $this->getUser()) {
                $member = $userMessage->getUser();
            }
        }

        if (false === $userInConversation) {
            throw $this->createNotFoundException();
        }

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

        $navbarConversations = $entityManager
            ->getRepository(UserMessengerConversation::class)
            ->findAllByUser($this->getUser(), 5)
        ;

        $userMessengerProvider->addExtraInfos($navbarConversations);

        $messages = [];
        foreach ($navbarConversations as $navbarConversation) {
            $messages[] = [
                'html' => $this->renderView('@UserMessenger/_message.html.twig', [
                    'page' => 'navbar',
                    'conversation' => $navbarConversation,
                    'message' => $navbarConversation->__lastMessage,
                    'previous_date' => null,
                    'userPrintedThisMessage' => $this->getUser(),
                ]),
                'uuid' => $navbarConversation->getUuid(),
            ];
        }

        $update = new Update(
            'https://bubble.lgbt/user/' . $this->getUser()->getSlug(),
            json_encode([
                'messages' => $messages,
            ]),
            true,
            null,
            'user_messenger_refresh_navbar'
        );
        $publisher($update);

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
