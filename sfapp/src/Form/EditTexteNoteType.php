<?php

namespace App\Form;

use App\Entity\Batiment;
use App\Entity\Note;
use App\Entity\Salle;
use Doctrine\DBAL\Types\TextType;
use PhpParser\Node\Expr\Array_;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditTexteNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('conseil', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Texte de la note',
                    'rows' => 3,
                    'maxlength' => 50,
                ],
                'label' => false,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Note::class,
        ]);
    }
}
