<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationFormType extends AbstractType
{   
    
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }



    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname',  TextType::class, [
                'label' => $this->translator->trans('register.firstname'),
                'label_attr' => [
                    'for' => 'floatingFirstname'
                ],
                'attr' => [
                    'class' => 'rounded-top rounded-bottom-0',
                    'id' => 'floatingFirstname'
                    
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                    
                ], 
            ])
            ->add('lastname',  TextType::class, [
                'label' => $this->translator->trans('register.lastname'),
                'attr' => [
                    'class' => 'rounded-0'

                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ], 
            ])
            ->add('email',  EmailType::class, [
                'label' => $this->translator->trans('register.email'),
                'attr' => [
                    'class' => 'rounded-0'
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ], 
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options'  => [
                    'label' => $this->translator->trans('register.password'),
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'class' => 'rounded-0 mb-0'
                    ],
                    'row_attr' => [
                        'class' => 'form-floating',
                    ], 
                ],
                'second_options' => [
                    'label' => $this->translator->trans('register.password_repeat'),
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                    'row_attr' => [
                        'class' => 'form-floating',
                    ], 
                ],
                'invalid_message' => $this->translator->trans('register.plainpassword_invalid_message'),
                'constraints' => [
                    new NotBlank([
                        'message' => $this->translator->trans('register.please_enter_password'),
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => $this->translator->trans('register.password_length'),
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => $this->translator->trans('register.agree_terms'),
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => $this->translator->trans('register.should_agree_terms'),
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
