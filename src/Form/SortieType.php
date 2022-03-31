<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participants;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('dateHeureDebut', DateTimeType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('duree', TimeType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('dateLimiteInscription', DateTimeType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('inscriptionMax', IntegerType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('description', TextType::class,[
                'attr'=>["class"=>"border border-primary"]
            ])
            //->add('participants',EntityType::class,['class'=>Participants::class,'choice_label'=>'nom','multiple'=>true])
            ->add('lieu',EntityType::class,[
                'class'=>Lieu::class,'choice_label'=>'nom',
                'attr'=>["class"=>"border border-primary"]
            ])
            ->add('site',EntityType::class,[
                'class'=>Site::class,'choice_label'=>'nom',
                'expanded'=>false,'multiple'=>false,
                'attr'=>["class"=>"border border-primary"]
            ])

            //->add('etat',EntityType::class,['class'=>Etat::class,'choice_label'=>'libelle'])
            //->add('organisateur',EntityType::class,['class'=>Participants::class,'choice_label'=>'nom'])
            ->add('valider',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
