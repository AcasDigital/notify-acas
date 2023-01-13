<?php

namespace App\Tests;

use App\Entity\EmploymentDispute;
use App\Entity\UsageReport;
use App\Services\UsageReportCreator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UsageReportCreatorTest extends TestCase
{
    public function testCreateFromDisputeStandard(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $usageCreator = new UsageReportCreator($entityManager);
        $employmentDispute = new EmploymentDispute();
        $employmentDispute->setCreated(new \DateTime('2022-01-01 01:00'));
        $employmentDispute->setSubmissionDateTime(new \DateTime('2022-01-01 02:30'));
        $employmentDispute->setType('example_type');
        $employmentDispute->setContactMethod('email');
        $employmentDispute->setRepresenting('example_representative');
        $employmentDispute->setMemorableWord('STINGRAY');
        $employmentDispute->setNumberOfReturns(3);
        $employmentDispute->setData(['sectionData' => ['no_matching']]);
        $usageReport = $usageCreator->createAndPersistFromDispute($employmentDispute);

        $this->assertInstanceOf(UsageReport::class, $usageReport);
        $this->assertEquals(5400, $usageReport->getTimeToSubmission());
        $this->assertEquals('example_type', $usageReport->getJourneyType());
        $this->assertTrue($usageReport->getEmailProvided());
        $this->assertEquals('example_representative', $usageReport->getRepresentative());
        $this->assertTrue($usageReport->getMemorableWordProvided());
        $this->assertEquals(0, $usageReport->getReasonForDisputeCount());
        $this->assertEquals(3, $usageReport->getNumberOfReturns());
    }

    public function testCreateFromDisputeEdge(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $usageCreator = new UsageReportCreator($entityManager);
        $employmentDispute = new EmploymentDispute();
        $employmentDispute->setCreated(new \DateTime('2022-01-01 01:00'));
        $employmentDispute->setSubmissionDateTime(new \DateTime('2022-01-03 01:00'));
        $employmentDispute->setType('example_type');
        $employmentDispute->setContactMethod('phone');
        $employmentDispute->setRepresenting('example_representative');
        $employmentDispute->setMemorableWord('');
        $employmentDispute->setData(['sectionData' => ['rfd' => ['rfd' => ['testPositive' => true, 'testNegative' => false, 'testNull' => null]]]]);
        $usageReport = $usageCreator->createAndPersistFromDispute($employmentDispute);

        $this->assertInstanceOf(UsageReport::class, $usageReport);
        $this->assertEquals(172800, $usageReport->getTimeToSubmission());
        $this->assertEquals('example_type', $usageReport->getJourneyType());
        $this->assertFalse($usageReport->getEmailProvided());
        $this->assertEquals('example_representative', $usageReport->getRepresentative());
        $this->assertFalse($usageReport->getMemorableWordProvided());
        $this->assertEquals(1, $usageReport->getReasonForDisputeCount());
        $this->assertEquals(0, $usageReport->getNumberOfReturns());
    }
}
