<?php

namespace App\Form;

use App\Entity\Etage;
use App\Entity\Salle;
use App\Entity\SystemAcquisition;
use App\Form\DetailsSalleType;
use App\Form\PlanType;
use App\Repository\SystemAcquisitionRepository;
use App\Repository\EtageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la salle : ',
                'required' => false,
                'attr' => [
                    'placeholder' => 'ex : D304', 
                    'class' => 'custom-select-class',
                    'maxlength' => 25,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ ne peut pas être vide.',
                    ]),
                ]
            ])
            ->add('etage', EntityType::class, [
                'class' => Etage::class,
                'choice_label' => 'nomComplet',
                'label' => 'Etage : ',
                'attr' => ['class' => 'custom-select-class'],
                'query_builder' => function(EtageRepository $etageRepository) use ($options) {
                    return $etageRepository->findAllByBatimentForm($options['v_batiment']);
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
            'v_batiment' => '',
        ]);
    }
}
