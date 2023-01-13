<?php

namespace App\Form\Admin;

use App\Entity\Admin\Translation;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('key');
        $editor = $options['editor'];
        if (!empty($options['editor']) && 'plain' === $options['editor']) {
            $builder->add('translation');
        } else {
            $builder->add('translation', CKEditorType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Translation::class,
            'editor' => ['plain', 'wysiwyg'],
        ]);
    }
}
