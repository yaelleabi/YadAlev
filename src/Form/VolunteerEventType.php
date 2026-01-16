<?php

namespace App\Form;

use App\Entity\Volunteer;
use App\Entity\VolunteerEvent;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VolunteerEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'html5' => true,
            ])
            ->add('endDate', \Symfony\Component\Form\Extension\Core\Type\DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'html5' => true,
            ])
            ->add('isVisible', null, [
                'label' => 'Est visible ?',
            ])
            ->add('Title', null, [
                'label' => 'Titre',
            ])
            ->add('Description', null, [
                'label' => 'Description',
            ])
            ->add('assignedVolunteers', EntityType::class, [
                'class' => Volunteer::class,
                'choice_label' => function (Volunteer $volunteer) {
                    return $volunteer->getFirstName() . ' ' . $volunteer->getName();
                },
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VolunteerEvent::class,
        ]);
    }
}
