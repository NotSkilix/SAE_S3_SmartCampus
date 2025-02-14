<?php
namespace App\Form;

use App\Entity\SystemAcquisition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AddSAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // TODO: It.3 - Lister les SA de l'API ?
            ->add('nom', TextType::class, [
                'label' => 'Nom du SA : ',
                'attr' => [
                    'placeholder' => 'ex : ESP-001',
                    'maxlength' => 10,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SystemAcquisition::class,
        ]);
    }
}
