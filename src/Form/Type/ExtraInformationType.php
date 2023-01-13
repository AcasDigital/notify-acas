<?php

namespace App\Form\Type;

use App\Form\Data\ExtraInformationData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ExtraInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hasExtra', ChoiceType::class, [
                'choices' => ['Yes' => 'yes', 'No' => 'no'],
                'expanded' => true,
            ]);
        if ('date' == $options['field_type']) {
            $builder->add('extraInformation', GovukDateType::class, [
                'label' => $options['extra_info_label'],
                'help' => $options['extra_info_help'],
            ]);
        } elseif ('money' == $options['field_type']) {
            $builder->add('extraInformation', MoneyType::class, [
                'label' => $options['extra_info_label'],
                'help' => $options['extra_info_help'],
                'currency' => $options['currency'],
                'scale' => $options['scale'],
            ]);
        } else {
            $constraints = [];
            if ($options['extra_maxlength'] ?? null) {
                assert(is_int($options['extra_maxlength']));
                $message = $options['maxlength_error'] ?? null;
                if (is_string($message)) {
                    $constraints = [new Length(max: $options['extra_maxlength'], maxMessage: $message)];
                } else {
                    $constraints = [new Length(max: $options['extra_maxlength'])];
                }
            }
            $builder->add('extraInformation', CharacterCountType::class, [
                'label' => $options['extra_info_label'],
                'help' => $options['extra_info_help'],
                'maxlength' => $options['extra_maxlength'],
                'constraints' => $constraints,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExtraInformationData::class,
            'extra_info_label' => null,
            'extra_info_help' => null,
            'extra_maxlength' => null,
            'maxlength_error' => null,
            'no_text' => null,
            'field_type' => null,
            'currency' => null,
            'scale' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['no_text'] = $options['no_text'];
    }

    public function getBlockPrefix()
    {
        return 'extrainformation';
    }
}
