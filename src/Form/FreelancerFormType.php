<?php

namespace App\Form;

use App\Entity\Freelancer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class FreelancerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hourlyRate', NumberType::class, [
                'required' => false,
                'html5' => true,
                'scale' => 2,
            ])
            ->add('yearsExperience', IntegerType::class, [
                'required' => false,
            ])
            ->add('category', EntityType::class, [
            'class' => Category::class,
            'choice_label' => 'name',
            'placeholder' => 'Select a category',
            'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Freelancer::class,
        ]);
    }
}