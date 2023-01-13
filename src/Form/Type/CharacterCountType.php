<?php

namespace App\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CharacterCountType extends TextareaType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        // makes it legal for FileType fields to have an image_property option
        $resolver->setDefaults(['maxlength' => 200, 'rows' => 5, 'label' => false, 'maxlength_error' => null]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['maxlength'] = $options['maxlength'];
        $view->vars['maxlength_error'] = $options['maxlength_error'];
        $view->vars['rows'] = $options['rows'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return TextareaType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'charactercount';
    }
}
