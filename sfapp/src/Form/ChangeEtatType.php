<?php

namespace App\Form;

use App\Entity\Plan;
use App\Entity\SystemAcquisition;
use App\Entity\Etat;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeEtatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Etat', EnumType::class, [
                'label' => false,
                'class' => Etat::class,
                'choice_label' => static function (\UnitEnum $choice): string {
                    return $choice->value;
                }
            ])
            // élément caché pour faire passer le nom du sa dont on change l'état
            ->add('isSubmit', HiddenType::class, [
                'mapped' => false,
                'data' => '',
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SystemAcquisition::class,
        ]);
    }
}
