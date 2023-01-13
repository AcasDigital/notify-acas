<?php

namespace App\EmploymentDispute\Validator;

use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\EmploymentDispute;
use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContentAwareFullNameValidationValidator extends ConstraintValidator
{
    public function __construct(private EmploymentDisputeRepository $repo, private RequestStack $requestStack)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ContentAwareFullNameValidation) {
            throw new UnexpectedTypeException($constraint, ContentAwareFullNameValidation::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) to take care of that
        if (null === $value || '' === $value) {
            return;
        }

        $routeParams = $this->requestStack->getMainRequest()->attributes->get('_route_params');

        $id = $routeParams['id'] ?? null;
        $disputeForm = null;
        $taskId = $routeParams['taskId'] ?? null;

        if ($id) {
            $disputeForm = $this->repo->find($id);
        }

        $representing = null;
        if ($disputeForm instanceof EmploymentDispute) {
            $representing = $disputeForm->getRepresenting();
        }

        if (TaskOptions::REPRESENTATIVE_MYSELF === $representing) {
            $representingText = 'your';
        } else {
            $representingText = "the claimant's";
        }

        // claimant name
        if (!$value->getFirstName() && 'claimant_name' === $taskId) {
            $this->context->buildViolation("Enter $representingText first name (including middle names)​")
                ->atPath('firstName')
                ->addViolation();
        }

        if (!$value->getLastName() && 'claimant_name' === $taskId) {
            $this->context->buildViolation("Enter $representingText last name or family name​")
                ->atPath('lastName')
                ->addViolation();
        }
    }
}
