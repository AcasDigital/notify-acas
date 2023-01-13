<?php

namespace App\Form\Type;

use App\Form\Data\OptionalAddressData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionalAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('addressFirstLine', TextType::class, [
                'required' => false,
                'label' => 'Address line 1',
            ])
            ->add('addressSecondLine', TextType::class, [
                'required' => false,
                'label' => 'Address line 2',
            ])
            ->add('town', TextType::class, [
                'required' => false,
                'label' => 'Town or city',
            ])
            ->add('postcode', TextType::class, [
                'required' => false,
                'label' => 'Postcode',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OptionalAddressData::class,
        ]);
    }
}
