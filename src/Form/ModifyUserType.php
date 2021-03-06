<?php

namespace App\Form;

use App\Entity\Participants;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;
use function Sodium\add;

class ModifyUserType extends AbstractType
{
    protected $auth;

    public function __construct(AuthorizationCheckerInterface $auth)
    {
        $this->auth = $auth;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ["class" => "border border-primary"],
                'label' => '*Adresse email',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^([a-zA-Z]+)(@)([a-zA-Z]+)(\.)([a-zA-Z]+)$/i", "L'adresse mail doit contenir un '@' et un '.' et ne peut pas contenir de caractères alphanumériques.")
                ]
            ])
            ->add('tel', TelType::class, [
                'attr' => ["class" => "border border-primary"],
                'label' => '*Numero de téléphone',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^(0|\+33)[1-9]( *[0-9]{2}){4}+$/i", "Le numéro de téléphone doit contenir 10 chiffres et commencer par un 0.")
                ]
            ])
            ->add('pseudo', TextType::class, [
                'attr' => ["class" => "border border-primary"],
                'label' => '*Pseudo',
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^[a-z\-0-9]+$/i", "Le pseudo peut seulement contenir des caractères alphanumériques.")
                ]
            ])
            ->add('Valider', SubmitType::class, [
                'attr' => ["class" => "btn btn-success"]
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
            ]);
        if ($this->auth->isGranted('ROLE_ADMIN')) {
            $builder
                ->add('site', EntityType::class, [
                    'attr' => ["class" => "border border-primary"],
                    'label' => '*Site',
                    'class' => Site::class,
                    'choice_label' => 'nom',
                    'expanded' => false,
                    'multiple' => false,
                ])
                ->add('nom', TextType::class, [
                    'attr' => ["class" => "border border-primary"],
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
                ->add('prenom', TextType::class, [
                    'attr' => ["class" => "border border-primary"],
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
               ->add('actif', CheckboxType::class,[
                  'attr'=>['class'=>'form-check-input',
                      'id'=>'flexSwitchCheckDefault'],
                   'required'=>false
                   ] );

                }


    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participants::class,
        ]);
    }
}
