<?php

namespace App\EmploymentDispute\Tasks;

class TaskOptions
{
    public const FLOW_CERTIFICATE = 'certificate';
    public const FLOW_EARLY_CONCILIATION = 'early-conciliation';
    public const REPRESENTATIVE_MYSELF = 'myself';
    public const REPRESENTATIVE_OTHER = 'other';
    public const CONTACT_METHOD_POST = 'post';
    public const CONTACT_METHOD_EMAIL = 'email';
    public const CONTACT_METHOD_PHONE_EMAIL = 'phone-email';
    public const CONTACT_METHOD_PHONE_POST = 'phone-post';
    public const CHOICE_YES_NO = ['Yes' => 'yes', 'No' => 'no'];
    public const CHOICE_RAISED_WITH = [
        '[your|their] employer or a relevant responsible person' => 'employer_or_relevant_person',
        '<a href="https://www.gov.uk/government/publications/blowing-the-whistle-list-of-prescribed-people-and-bodies--2" target="_blank">a prescribed person or body (opens in a new window or tab)</a>' => 'regulator',
        'anyone else' => 'anyone',
        ];
    public const CHOICE_IS_IT_ABOUT_WHISTLEBLOWING = [
      'a crime' => 'crime',
      'the breach of a legal obligation' => 'breach_of_a_legal_obligation',
      'a miscarriage of justice' => 'miscarrige_of_justice',
      'health and safety' => 'health_and_safety',
      'damage to the environment' => 'damage_to_the_environment',
      'a cover up of any of the above' => 'cover_up',
    ];

    public const CHOICE_WHY_DISCRIMINATED = [
        'Age' => 'age',
        'Disability' => 'disability',
        'Gender reassignment' => 'gender_reassignment',
        'Marriage or civil partnership' => 'marriage',
        'Pregnancy or maternity' => 'maternity',
        'Religion or belief' => 'religion',
        'Race' => 'race',
        'Sex (including equal pay)' => 'sex',
        'Sexual orientation' => 'sexual_orientation',
    ];

    public const REVIEW_DISPLAY_RAISED_WITH = [
        'employer_or_relevant_person' => 'employer or relevant responsible person',
        'regulator' => 'prescribed person or body',
        'anyone' => 'anyone else',
    ];

    private ?string $flow;
    private ?string $representative;
    /**
     * @var string[]
     */
    private array $contactMethods;

    public function getRepresentative(): ?string
    {
        return $this->representative;
    }

    public function setRepresentative(?string $representative): self
    {
        $this->representative = $representative;

        return $this;
    }

    public function getFlow(): ?string
    {
        return $this->flow;
    }

    public function setFlow(?string $flow): self
    {
        $this->flow = $flow;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getContactMethods(): array
    {
        return $this->contactMethods;
    }

    public function setContactMethods(?string $contactMethod): self
    {
        if (null === $contactMethod) {
            $this->contactMethods = [];

            return $this;
        }

        $this->contactMethods = explode('-', $contactMethod);

        return $this;
    }

    public function hasPhone(): bool
    {
        return in_array('phone', $this->getContactMethods());
    }

    public function hasPost(): bool
    {
        return in_array('post', $this->getContactMethods());
    }

    public function hasEmail(): bool
    {
        return in_array('email', $this->getContactMethods());
    }

    /**
     * @return array|string[]
     */
    public function getChoiceOptions(string $id)
    {
        return match ($id) {
            'yes_no' => self::CHOICE_YES_NO,
            'raised_with' => self::CHOICE_RAISED_WITH,
            'is_it_about_whistleblowing' => self::CHOICE_IS_IT_ABOUT_WHISTLEBLOWING,
            'why_discriminated' => self::CHOICE_WHY_DISCRIMINATED,
            default => [],
        };
    }
}
