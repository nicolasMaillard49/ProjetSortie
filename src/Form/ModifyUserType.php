<?php

namespace App\Form;

use App\Entity\Participants;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class ModifyUserType extends AbstractType
{
    protected $auth;

    public function __construct(AuthorizationCheckerInterface $auth) {
        $this->auth = $auth;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,[
                'attr'=>["class"=>"border border-primary"],
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^([a-zA-Z]+)(@)([a-zA-Z]+)(\.)([a-zA-Z]+)$/i", "L'adresse mail doit contenir un '@' et un '.' et ne peut pas contenir de caractères alphanumériques.")
                ]
            ])
            ->add('tel', TelType::class,[
                'attr'=>["class"=>"border border-primary"],
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^(0|\+33)[1-9]( *[0-9]{2}){4}+$/i", "Le numéro de téléphone doit contenir 10 chiffres.")
                ]
            ])
            ->add('pseudo', TextType::class,[
                'attr'=>["class"=>"border border-primary"],
                'constraints' => [
                    new NotBlank(),
                    new NotNull(),
                    new Regex("/^[a-z\-0-9]+$/i", "Le pseudo ne doit contenir que des caractères alphanumériques.")
                ]
            ])

            ->add('Modifier', SubmitType::class)
        ;
        if($this->auth->isGranted('ROLE_ADMIN')){
            $builder->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'expanded'=> false,
                'multiple'=>false,
                'attr' => ['class' => 'border border-primary'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participants::class,
        ]);
    }
}
