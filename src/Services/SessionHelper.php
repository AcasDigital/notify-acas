<?php

namespace App\Services;

use App\Entity\EmploymentDispute;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SessionHelper
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function setCurrentForm(EmploymentDispute $employmentDispute): void
    {
        $session = $this->requestStack->getSession();

        $session->set('currentForm', $employmentDispute->getId());
        $session->save();
    }

    public function denyUnlessAccessToForm(EmploymentDispute $employmentDispute): void
    {
        $session = $this->requestStack->getSession();
        $currentForm = $session->get('currentForm');

        if ($currentForm !== $employmentDispute->getId()) {
            throw new AccessDeniedException();
        }
    }
}
