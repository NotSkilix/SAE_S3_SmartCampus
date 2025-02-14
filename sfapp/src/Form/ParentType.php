<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\DetailsSalleType;
use App\Form\PlanType;
use App\Form\AddSalleType;

class ParentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('salle', AddSalleType::class, [
                'data' => $options['v_salle'],
                'v_batiment' => $options['v_batiment'],
                'label' => false,
            ])
            ->add('detailsSalle', DetailsSalleType::class, [
                'data' => $options['v_details'],
                'label' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'v_salle' => null,
            'v_details' => null,
            'v_plans' => null,
            'v_batiment' => '',
        ]);
    }
}
