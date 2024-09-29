<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class EditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Bitte laden Sie ein gültiges Bild hoch (PNG, JPEG oder GIF).',
                    ])
                ],
            ])
            ->add('firstname',  TextType::class, [
                'label' => 'Firstname',
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
                'label' => 'Lastname',
                'attr' => [
                    'class' => 'rounded-bottom rounded-top-0'
                ],
                'row_attr' => [
                    'class' => 'form-floating',
                ], 
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save',
                'row_attr' => [
                    'class' => 'd-flex justify-content-center',
                    
                ], 
                'attr' => [
                    'class' => 'btn btn-primary d-flex align-items-center mt-3',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => ['id' => 'img-upload-form',
                        'class' => '']
        ]);
    }
}
