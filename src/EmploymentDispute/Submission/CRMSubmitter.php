<?php

namespace App\EmploymentDispute\Submission;

use App\Entity\EmploymentDispute;
use App\Entity\EmploymentDisputeSubmission;
use App\Repository\EmploymentDisputeSubmissionRepository;
use Symfony\Component\HttpClient\Exception\TimeoutException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CRMSubmitter
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private PayloadGenerator $payloadGenerator,
        private EmploymentDisputeSubmissionRepository $submissionRepository,
        private string $crmSubmissionEndpoint,
        private string $crmSubmissionSubscriptionKey,
    ) {
    }

    public function submit(EmploymentDispute $employmentDispute, ?string $customPayload): bool
    {
        if ($customPayload) {
            $jsonPayload = $customPayload;
        } else {
            $jsonPayload = $this->payloadGenerator->generateJSONPayload($employmentDispute);
        }

        $submission = (new EmploymentDisputeSubmission())
            ->setCreated(new \DateTime())
            ->setPayload($jsonPayload)
            ->setEmploymentDispute($employmentDispute);

        try {
            $options = [
                'headers' => [
                    'Ocp-Apim-Subscription-Key' => $this->crmSubmissionSubscriptionKey,
                    'Content-Type' => 'application/json',
                ],
                'body' => $jsonPayload,
                'timeout' => 300, // The API response can sometimes be very slow, so this timeout is suitably large.
            ];
            $request = $this->httpClient->request('POST', $this->crmSubmissionEndpoint, $options);
            $response = $request->getContent();
            $submission->setResponse($response)
                ->setStatus(EmploymentDisputeSubmission::STATUS_SUCCESS);
            $this->submissionRepository->add($submission);
        } catch (\Exception $e) {
            if ($e->getPrevious() instanceof TimeoutException) {
                // If the API times out we do not want to retry - as this could cause duplicates in CRM.
                throw new UnrecoverableMessageHandlingException('API Timeout. Do not retry.');
            }
            if ($e instanceof HttpExceptionInterface) {
                $response = sprintf("%s \n\n %s", $e->getMessage(), $e->getResponse()->getContent(false));
            } else {
                $response = $e->__toString();
            }
            $submission->setResponse($response)
                ->setStatus(EmploymentDisputeSubmission::STATUS_ERROR);

            $this->submissionRepository->add($submission);
            throw $e;
        }

        return true;
    }
}
