<?php

namespace App\Form\Type;

use App\Form\Data\PhoneData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class PhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $maxLength = 200;
        $builder
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone number',
            ])
            ->add('mobileConfirmation', CheckboxType::class, [
                'label' => 'Tick this box if you have entered a mobile number',
            ])
            ->add('alternativeNumber', TextType::class, [
                'label' => 'Another number',
            ])
            ->add('extraInformation', CharacterCountType::class, [
                'label' => false,
                'maxlength' => $maxLength,
                'constraints' => [new Length(max: $maxLength, maxMessage: "The text must be $maxLength characters or less")],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PhoneData::class,
            'maxlength_error' => null,
        ]);
    }
}
