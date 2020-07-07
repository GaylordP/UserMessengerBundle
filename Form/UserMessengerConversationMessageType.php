<?php

namespace GaylordP\UserMessengerBundle\Form;

use GaylordP\UploadBundle\Form\Type\UploadType;
use GaylordP\UserMediaBundle\Entity\UserMedia;
use GaylordP\UserMediaBundle\Repository\UserMediaRepository;
use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationMessage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class UserMessengerConversationMessageType extends AbstractType
{
    private $security;
    private $userMediaRepository;

    public function __construct(
        Security $security,
        UserMediaRepository $userMediaRepository
    ) {
        $this->security = $security;
        $this->userMediaRepository = $userMediaRepository;
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $userMedias = $this->userMediaRepository->findByCreatedBy($this->security->getUser(), [
            'id' => 'DESC',
        ]);

        $builder
            ->add('message', null, [
                'label' => false,
                'ico' => 'fas fa-pencil-alt',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'label.your_message',
                ],
                'translation_domain' => 'user_messenger',
            ])
            ->add('upload', UploadType::class, [
                'label' => 'label.media',
                'translation_domain' => 'user_media',
                'mapped' => false,
                'row_attr' => [
                    'class' => 'd-none',
                ],
            ])
            ->add('userMedias', null, [
                'label' => false,
                'choices' => $userMedias,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => false,
                'row_attr' => [
                    'class' => 'd-none',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserMessengerConversationMessage::class,
        ]);
    }
}
