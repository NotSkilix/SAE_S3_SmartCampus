<?php

namespace App\Form;

use App\Entity\DetailsSalle;
use App\Entity\Salle;
use App\Entity\Exposition;
use App\Entity\Frequentation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;

class DetailsSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('superficie', NumberType::class, [
                'required' => false,
                'label' => "Superficie : ",
                'attr' => [
                    'class' => 'custom-select-class', 
                    'placeholder' => 'm²',
                    'maxlength' => 3,
                ],
                'invalid_message' => 'Ce champs doit être un nombre',
                'constraints' => [
                    new Positive([
                        'message' => 'La valeur doit être positive'
                    ]),
                ],
            ])
            ->add('fenetre', NumberType::class, [
                'required' => false,
                'label' => "Fenêtres : ",
                'attr' => [
                    'class' => 'custom-select-class', 
                    'placeholder' => 'ex : 3',
                    'maxlength' => 2,
                ],
                'invalid_message' => 'Ce champs doit être un nombre',
                'constraints' => [
                    new Positive([
                        'message' => 'La valeur doit être positive'
                    ])
                ],
            ])
            ->add('exposition', EnumType::class, [
                'label' => "Exposition : ",
                'class' => Exposition::class,
                'attr' => ['class' => 'custom-select-class'],
                'choice_label' => static function (\UnitEnum $choice): string {
                    return $choice->value;
                }
            ])
            ->add('radiateur', NumberType::class, [
                'required' => false,
                'label' => "Radiateurs : ",
                'attr' => [
                    'class' => 'custom-select-class', 
                    'placeholder' => 'ex : 3',
                    'maxlength' => 2,
                ],
                'invalid_message' => 'Ce champs doit être un nombre',
                'constraints' => [
                    new Positive([
                        'message' => 'La valeur doit être positive'
                    ])
                ],
            ])
            ->add('frequentation', EnumType::class, [
                'label' => "Fréquentation : ",
                'class' => Frequentation::class,
                'attr' => ['class' => 'custom-select-class'],
                'choice_label' => static function (\UnitEnum $choice): string {
                    return $choice->value;
                }
            ])
            ->add('porte', NumberType::class, [
                'required' => false,
                'label' => "Porte : ",
                'attr' => [
                    'class' => 'custom-select-class', 
                    'placeholder' => 'ex : 3',
                    'maxlength' => 2,
                ],
                'invalid_message' => 'Ce champs doit être un nombre',
                'constraints' => [
                    new Positive([
                        'message' => 'La valeur doit être positive'
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DetailsSalle::class,
        ]);
    }
}
