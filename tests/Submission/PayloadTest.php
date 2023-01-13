<?php

namespace App\Tests\Submission;

use App\EmploymentDispute\Sections\ReasonsForDisputeSection;
use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\Submission\PayloadNormaliser;
use App\EmploymentDispute\Tasks\TaskLocator;

use function PHPUnit\Framework\assertEquals;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PayloadTest extends TestCase
{
    protected MockObject $payloadNormaliserMock;
    protected Payload $payload;

    protected function setUp(): void
    {
        $this->payloadNormaliserMock = $this->createMock(PayloadNormaliser::class);
        $this->payload = new Payload($this->payloadNormaliserMock);
    }

    /**
     * @dataProvider mappingProvider
     */
    public function testJurisdicationMapping($types, $extraReasons, $expected): void
    {
        $payloadNormaliserMock = $this->createMock(PayloadNormaliser::class);
        $payload = new Payload($payloadNormaliserMock);
        $payload->setIncidentType($types, $extraReasons);
        $mappings = explode(',', $payload->incidentType);

        sort($mappings);
        sort($expected);

        assertEquals($expected, $mappings);
    }

    public function mappingProvider(): array
    {
        return [
            [['discrimination'], [], ['602700003']],
            [['discrimination'], ['age'], ['602700007']],
            [['discrimination', 'wages'], ['age'], ['602700007', '602700000']],
            [['discrimination', 'wages'], [], ['602700003', '602700000']],
            [['other'], [], ['602700005']],
            [['discrimination'], ['maternity'], ['602700010', '602700009']],
        ];
    }

    /**
     * @dataProvider threeMontshDataProvider
     */
    public function testOutOfTimeFlag(string $incidentDate, string $submissionDate, bool $expectedResult): void
    {
        $incidentDateTime = new \DateTime($incidentDate, new \DateTimeZone('UTC'));
        $submissionDateTime = new \DateTime($submissionDate, new \DateTimeZone('UTC'));

        $taskLocatorMock = $this->createMock(TaskLocator::class);
        $rfdSection = new ReasonsForDisputeSection($taskLocatorMock);
        $rfdSection->setOutOfTime($incidentDateTime, $this->payload, $submissionDateTime);

        $this->assertEquals($this->payload->outOfTime, $expectedResult);
    }

    public function threeMontshDataProvider(): array
    {
        return [
            ['2022-08-29', '2022-11-28', false],
            ['2022-08-29', '2022-11-29', true],
            ['2022-08-30', '2022-11-29', false],
            ['2022-08-30', '2022-11-30', true],
            ['2022-08-31', '2022-11-30', false],
            ['2022-08-31', '2022-12-01', true],
            ['2022-11-27', '2023-02-26', false],
            ['2022-11-27', '2023-02-27', true],
            ['2022-11-28', '2023-02-27', false],
            ['2022-11-28', '2023-02-28', true],
            ['2022-11-29', '2023-02-28', false],
            ['2022-11-29', '2023-03-01', true],
            ['2022-11-30', '2023-02-28', false],
            ['2022-11-30', '2023-03-01', true],
            ['2022-12-01', '2023-02-28', false],
            ['2022-12-01', '2023-03-01', true],
            ['2022-12-02', '2023-03-01', false],
            ['2022-12-02', '2023-03-02', true],
            ['2022-09-30', '2022-12-29', false],
            ['2022-09-30', '2022-12-30', true],
        ];
    }

    /**
     * @dataProvider sixMontshDataProvider
     */
    public function testOutOfTimeRedundancyFlag(string $incidentDate, string $submissionDate, bool $expectedResult): void
    {
        $incidentDateTime = new \DateTime($incidentDate, new \DateTimeZone('UTC'));
        $submissionDateTime = new \DateTime($submissionDate, new \DateTimeZone('UTC'));

        $taskLocatorMock = $this->createMock(TaskLocator::class);
        $rfdSection = new ReasonsForDisputeSection($taskLocatorMock);

        $rfdSection->setOutOfTimeRedundancy($incidentDateTime, $this->payload, $submissionDateTime);

        $this->assertEquals($this->payload->outOfTime6Months, $expectedResult);
    }

    public function sixMontshDataProvider(): array
    {
        return [
            ['2022-08-31', '2023-02-28', false],
            ['2022-08-31', '2023-03-01', true],
        ];
    }
}
