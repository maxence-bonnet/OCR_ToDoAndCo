<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', null , [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez saisir un nom d\'utilisateur',
                    ]),
                ],
            ])
            ->add('email', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez saisir une adresse email',
                    ]),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => User::USER_ROLES,
                'multiple' => false,
                'expanded' => false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                    'constraints' => [
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Le mot de passe doit contenir au minimum {{ limit }} caractères',
                            'max' => 1000,
                        ]),
                    ],
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Les mots de passe sasis ne correspondent pas',
                'mapped' => false,
                'required' => false,
            ])
        ;
        
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
            function ($array) { // array to string
                 return count($array) ? $array[0] : null;
            },
            function ($string) { // string to array
                 return [$string];
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
