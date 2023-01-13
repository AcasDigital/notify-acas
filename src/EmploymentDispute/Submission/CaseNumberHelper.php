<?php

namespace App\EmploymentDispute\Submission;

use App\Entity\CaseNumber;
use App\Repository\EmploymentDisputeRepository;

class CaseNumberHelper
{
    public function __construct(private CaseNumberGenerator $caseNumberGenerator, private EmploymentDisputeRepository $employmentDisputeRepository)
    {
    }

    /**
     * Create case numbers with respondent data included.
     *
     * @param array<mixed> $respondentSectionData
     *
     * @return array<CaseNumber>
     */
    public function createCaseNumbers(array $respondentSectionData): array
    {
        $caseNumbers = [];
        foreach ($respondentSectionData as $respondentData) {
            assert(is_array($respondentData));
            // the respondent data is mandatory but we set the values to a string
            // by default to avoid FE errors for the user
            $name = '';

            $respondentName = isset($respondentData['org_name']) ? $respondentData['org_name'] : (isset($respondentData['person_name']) ? $respondentData['person_name'] : null);
            $orgFullName = $respondentName['orgName'] ?? null;
            $personFullName = $respondentName['fullName'] ?? null;

            if ($orgFullName) {
                $name = $orgFullName;
            } elseif (is_array($personFullName) && !empty($personFullName)) {
                $firstName = $personFullName['firstName'] ?? null;
                $lastName = $personFullName['lastName'] ?? null;

                $name = "$firstName $lastName";
            }

            $respondentData = $respondentData['respondent'] ?? '';
            $respondent = $respondentData['data'] ?? '';

            $caseNumber = new CaseNumber();

            $caseNumber->setRespondentName($name);
            $caseNumber->setRespondentType($respondent);
            $caseNumber->setCaseRefNumber($this->caseNumberGenerator->generateCaseNumber());

            $caseNumbers[] = $caseNumber;
        }

        return $caseNumbers;
    }
}
