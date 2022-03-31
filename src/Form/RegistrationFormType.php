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
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('nom', TextType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('prenom', TextType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('pseudo', TextType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('tel', TelType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('site', EntityType::class,[
                'class'=>Site::class,'choice_label'=>'nom',
                'attr'=>["class"=>"border border-primary"]
                ])

            //->add('actif')
           // ->add('organisateur')

            ->add('plainPassword', PasswordType::class,[
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password', "class"=>"border border-primary"],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

//            ->add('file', FileType::class,[
//                'label' => 'Photo',
//                'mapped' => false,
//                'required'=>false,
//                'constraints'=>[
//                    new File([
//                        'maxSize' => '1024k',
//                        'mimeTypes' =>[
//                            'application/JPEG',
//                            'application/JPG',
//                            'application/PNG',
//                        ],
//                        'mimeTypesMessage' =>'Veuillez déposer une image au format jpeg, jpg ou png svp',
//                    ])
//                ],
//            ])


            ->add('agreeTerms', CheckboxType::class, [
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
