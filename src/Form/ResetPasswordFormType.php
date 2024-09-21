<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class ResetPasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('plainPassword', RepeatedType::class, [
            // instead of being set onto the object directly,
            // this is read and encoded in the controller
            'type' => PasswordType::class,
            'mapped' => false,
            'first_options'  => [
                'label' => 'Password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'rounded-top rounded-bottom-0 mb-0'
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ], 
            ],
            'second_options' => [
                'label' => 'Repeat Password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'mb-3',
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ], 
            ],
            'invalid_message' => 'The password fields must match.',
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter a password',
                ]),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Your password should be at least {{ limit }} characters',
                    // max length allowed by Symfony for security reasons
                    'max' => 4096,
                ]),
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
