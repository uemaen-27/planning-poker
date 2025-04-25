<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StartPageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
  $builder
            ->add('username', TextType::class, [
                'label' => 'Dein Username',
            ])
            ->add('sessionCode', TextType::class, [
                'label' => 'Session Code (optional)',
                'required' => false,
            ])
            ->add('revealMode', CheckboxType::class, [
                'label' => 'Karten sofort aufdecken',
                'required' => false,
                'mapped' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}