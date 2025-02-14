<?php

namespace App\Form;

use App\Entity\Plan;
use App\Entity\Salle;
use App\Entity\SystemAcquisition;
use App\Repository\SystemAcquisitionRepository;
use App\Repository\SalleRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salle', EntityType::class, [
                'class' => Salle::class,
                'label' => 'Salle : ',
                'choice_label' => 'nom',
                'query_builder' => function(SalleRepository $salleRepository) use ($options) {
                    return $salleRepository->findByBatiment($options['v_batiment']);
                }
            ])
            ->add('SA', EntityType::class, [
                'class' => SystemAcquisition::class,
                'label' => false,
                'choice_label' => 'nom',
                'query_builder' => function(SystemAcquisitionRepository $systemeAcquisitionRepository){
                    return $systemeAcquisitionRepository->getAllAvalailableSA();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plan::class,
            'v_batiment' => '',
        ]);
    }
}
