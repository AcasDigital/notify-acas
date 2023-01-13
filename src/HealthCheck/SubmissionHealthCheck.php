<?php

namespace App\HealthCheck;

use App\Entity\EmploymentDispute;
use App\Repository\EmploymentDisputeRepository;

class SubmissionHealthCheck implements HealthCheckInterface
{
    public function __construct(private EmploymentDisputeRepository $employmentDisputeRepository)
    {
    }

    public function check(): HealthCheckResponse
    {
        $response = new HealthCheckResponse();
        $response->setLabel('Submissions');

        $failed = count($this->employmentDisputeRepository->findBy(['status' => EmploymentDispute::STATUS_FAILED]));

        if ($failed > 0) {
            $response->addError("There are $failed failed submissions.");
        }

        return $response;
    }
}
