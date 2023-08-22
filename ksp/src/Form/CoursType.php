<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\TypeCours;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCours', EntityType::class, [
                'class' => TypeCours::class,
                'choice_label' => 'libelle',
                'required'=>false,
                'attr' => [
                    'id' => 'nomCours',
                    'v-model' => 'nomCours'
                ]
            ])
            ->add('date_cours', DateType::class, [
                'widget' => 'single_text',
                "required" => false,
                'attr' => [
                    'id' => 'date_cours',
                    'v-model' => 'dateCours'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}
