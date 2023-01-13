<?php

namespace App\Services;

use App\Entity\EmploymentDispute;
use App\Entity\UsageReport;
use Doctrine\ORM\EntityManagerInterface;

class UsageReportCreator
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createAndPersistFromDispute(EmploymentDispute $employmentDispute): UsageReport
    {
        $usageReport = $this->createFromDispute($employmentDispute);
        $this->entityManager->persist($usageReport);
        $this->entityManager->flush();

        return $usageReport;
    }

    private function createFromDispute(EmploymentDispute $employmentDispute): UsageReport
    {
        $usageReport = new UsageReport();

        $usageReport->setCreated($employmentDispute->getCreated());
        $usageReport->setSubmitted($employmentDispute->getSubmissionDateTime());
        $usageReport->setTimeToSubmission($employmentDispute->getSubmissionDateTime()->getTimestamp() - $employmentDispute->getCreated()->getTimestamp());

        $usageReport->setJourneyType($employmentDispute->getType());
        $usageReport->setEmailProvided(str_contains($employmentDispute->getContactMethod(), 'email'));
        $usageReport->setRepresentative($employmentDispute->getRepresenting());
        $usageReport->setMemorableWordProvided(strlen($employmentDispute->getMemorableWord()) > 0);
        $usageReport->setContactMethod($employmentDispute->getContactMethod());

        $data = $employmentDispute->getData();
        // @todo Direct access on the json payload should be discouraged.
        $rfds = $data['sectionData']['rfd']['rfd'] ?? [];
        $rfds = array_filter($rfds);
        $usageReport->setReasonForDisputeCount(count($rfds));

        $usageReport->setNumberOfReturns($employmentDispute->getNumberOfReturns());

        return $usageReport;
    }
}
