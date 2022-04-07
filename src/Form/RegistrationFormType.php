<?php

namespace App\Form;

use App\Entity\Participants;
use App\Entity\Site;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'attr'=>["class"=>"border border-primary"],
                'label' => '*Adresse email',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^([a-zA-Z\-0-9]+)(@)([a-zA-Z\-0-9]+)(\.)([a-zA-Z\-0-9]+)$/i", "L'adresse mail doit contenir un '@' et un '.' et ne peut pas contenir de caractères alphanumériques.")
                ]
            ])
            ->add('nom', TextType::class,[
                'attr'=>["class"=>"border border-primary"],
                'label' => '*Nom',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au minimum {{ limit }} caractères.',
                        'max' => 50,
                    ]),
                    new Regex("/^[a-zA-Z]+$/i", "Le nom ne doit contenir que des lettres.")
                ]
            ])
            ->add('prenom', TextType::class,[
                'attr'=>["class"=>"border border-primary"],
                'label' => '*Prenom',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le prénom doit contenir au minimum {{ limit }} caractères.',
                        'max' => 50,
                    ]),
                    new Regex("/^[a-zA-Z]+$/i", "Le prénom ne doit contenir que des lettres.")
                ]
            ])
            ->add('pseudo', TextType::class,[
                'attr'=>["class"=>"border border-primary"],
                'label' => '*Pseudo',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^[a-z\-0-9]+$/i", "Le pseudo peut seulement contenir des caractères alphanumériques.")
                ]
            ])
            ->add('tel', TelType::class,[
                'attr'=>["class"=>"border border-primary"],
                'label' => '*Numero de téléphone',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^(0|\+33)[1-9]( *[0-9]{2}){4}+$/i", "Le numéro de téléphone doit contenir 10 chiffres et commencer par un 0.")
                ]
            ])
            ->add('site', EntityType::class,[
                'class'=>Site::class,'choice_label'=>'nom',
                'label' => '*Site',
                'attr'=>["class"=>"border border-primary"]
                ])

            ->add('plainPassword', PasswordType::class,[
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', "class"=>"border border-primary"],
                'label' => '*Mot de passe',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner un mot de passe',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Votre mot de passe doit contenir au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

            ->add('images', FileType::class,[
                'attr'=>["class"=>"border border-primary"],
                'label' => 'Photo de profil',
                'mapped' => false,
                'required'=>false,
                'constraints'=>[
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' =>[
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                        ],
                        'mimeTypesMessage' =>'Veuillez déposer une image au format jpeg, jpg ou png svp',
                    ])
                ],
            ])


            ->add('agreeTerms', CheckboxType::class, [
                'attr'=>["class"=>"border border-primary"],
                'label' => 'Accepter les termes',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('Valider',SubmitType::class)
        ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participants::class,
        ]);
    }
}
