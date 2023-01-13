<?php

namespace App\EmploymentDispute\Validator;

use App\EmploymentDispute\Wizard\WizardCreator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VerificationCodeIsValidValidator extends ConstraintValidator
{
    public function __construct(private string $expiryMinutes, private WizardCreator $wizard)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof VerificationCodeIsValid) {
            throw new UnexpectedTypeException($constraint, VerificationCodeIsValid::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        $wizardData = $this->wizard->getData();

        $now = new \DateTime();
        $expiryMinutes = $this->expiryMinutes;

        $verificationCodeExpiry = $wizardData->getVerificationCodeStartDate()?->add(\DateInterval::createFromDateString($expiryMinutes.'mins'));

        if ($now > $verificationCodeExpiry) {
            $this->wizard->clearConfirmationData();

            $this->context->buildViolation('The confirmation code has expired. Request a new code below.')->addViolation();
        } elseif ($wizardData->getVerificationCode() !== $value) {
            $this->wizard->clearConfirmationData();
            if ($this->wizard->hasSelectedContact(['email', 'phone-email'])) {
                $contactType = 'email';
            } elseif ($this->wizard->hasSelectedContact(['phone-post'])) {
                $contactType = 'text';
            } else {
                $contactType = '';
            }
            $this->context->buildViolation("The confirmation code doesn't match. Enter the confirmation code you received by $contactType. The code is 5 numbers only.")->addViolation();
        }
    }
}
