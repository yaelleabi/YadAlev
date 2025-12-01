<?php

namespace App\Form;

use App\Entity\AidList;
use App\Entity\AidRequest;
use App\Entity\Family;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FamilyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password')
            ->add('name')
            ->add('phoneNumber')
            ->add('roles')
            ->add('aidList', EntityType::class, [
                'class' => AidList::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('aidRequest', EntityType::class, [
                'class' => AidRequest::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Family::class,
        ]);
    }
}
