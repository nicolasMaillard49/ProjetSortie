<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participants;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ["class" => "border border-primary"]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                "widget" => "single_text",
                'attr' => ["class" => "border border-primary",
                ]
            ])
            ->add('duree', TimeType::class, [
                'attr' => ["class" => "border border-primary"]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                "widget" => "single_text",
                'attr' => ["class" => "border border-primary"]
            ])
            ->add('inscriptionMax', IntegerType::class, [
                'attr' => ["class" => "border border-primary"]
            ])
            ->add('description', TextType::class, [
                'attr' => ["class" => "border border-primary"]
            ])
            ->add('ville', EntityType::class, [
                    'class' => 'App\Entity\Ville',
                    'mapped' => false,
                    'choice_label' => 'nom',
                    'placeholder' => 'Selectionnez une ville',
                    'required' => false
                ]

            );

        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $this->addLieuField($form->getParent(), $form->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {
                $data = $event->getData();
                /* @var $lieu \App\Entity\Lieu */
                $lieu = $data->getLieu();
                $form = $event->getForm();
                if ($lieu) {
                    $ville = $lieu->getVille();
                    $this->addLieuField($form, $ville);
                    $form->get('ville')->setData($ville);
                } else {
                    $this->addLieuField($form, null);
                }

            }
        )
        ;
    }

    private function addLieuField(FormInterface $form, ?Ville $ville){
        $builder = $form->add('lieu', EntityType::class,[
            'class' => Lieu::class,
            'choice_label' => 'nom',
            'placeholder' => $ville ? 'Selectionnez votre lieu' : 'Selectionnez votre ville',
            'required' => true,
            'auto_initialize' => false,
            'choices' => $ville ? $ville->getLieu() : []
        ]);
    }



    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
