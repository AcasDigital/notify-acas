<?php

namespace App\EmploymentDispute\Submission;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaseNumberGenerator
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $caseReferenceGeneratorEndpoint,
    ) {
    }

    public function generateCaseNumber(): string
    {
        // @todo What happens if the number generator returns a non-200 response?
        // Current we just let the exception bubble up.
        $response = $this->httpClient->request('GET', $this->caseReferenceGeneratorEndpoint);

        return $response->getContent();
    }
}
