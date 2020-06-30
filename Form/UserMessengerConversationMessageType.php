<?php

namespace GaylordP\UserMessengerBundle\Form;

use GaylordP\UserMessengerBundle\Entity\UserMessengerConversationMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserMessengerConversationMessageType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserMessengerConversationMessage::class,
        ]);
    }
}
