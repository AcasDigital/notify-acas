<?php

namespace App\Form\Type;

use App\Entity\EmploymentDispute;
use App\Form\Data\SubmissionSearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmissionSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $statusChoices = [
            EmploymentDispute::STATUS_QUEUED => EmploymentDispute::STATUS_QUEUED,
            EmploymentDispute::STATUS_PAUSED => EmploymentDispute::STATUS_PAUSED,
            EmploymentDispute::STATUS_SUBMITTED => EmploymentDispute::STATUS_SUBMITTED,
            EmploymentDispute::STATUS_DRAFT => EmploymentDispute::STATUS_DRAFT,
            EmploymentDispute::STATUS_FAILED => EmploymentDispute::STATUS_FAILED,
        ];

        $builder
            ->add('dateFrom', GovukDateType::class, [
                'label' => 'Submission date from',
            ])
            ->add('dateTo', GovukDateType::class, [
                'label' => 'Submission date to',
            ])
            ->add('caseNumber', TextType::class, [
                'label' => 'Case Reference Number',
            ])
            ->add('claimentOrRepFirstName', TextType::class, [
                'label' => 'Claimant or representative first name',
            ])
            ->add('claimentOrRepLastName', TextType::class, [
                'label' => 'Claimant or representative last name',
            ])
            ->add('respondentName', TextType::class, [
                'label' => 'Respondent name',
                ])
            ->add('returnCode', TextType::class)
            ->add('hasFailedSubmissions', ChoiceType::class, [
                'label' => 'Has failed submissions',
                'choices' => [
                    'Yes' => 'yes',
                    'No' => 'no',
                ],
                'placeholder' => 'Choose an option',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubmissionSearchData::class,
        ]);
    }
}
