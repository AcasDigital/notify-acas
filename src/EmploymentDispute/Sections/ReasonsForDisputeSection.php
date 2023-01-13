<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\Tasks\ConfigFileTask;
use App\EmploymentDispute\Tasks\Data\ArrayTaskData;
use App\EmploymentDispute\Tasks\Data\ChoiceExtraTaskData;
use App\EmploymentDispute\Tasks\Data\DateTaskData;
use App\EmploymentDispute\Tasks\Data\ExtraInformationTaskData;
use App\EmploymentDispute\Tasks\Data\HolidayDateTaskData;
use App\EmploymentDispute\Tasks\Data\MoneyTaskData;
use App\EmploymentDispute\Tasks\Data\OptionalStringTaskData;
use App\EmploymentDispute\Tasks\TaskInterface;
use App\EmploymentDispute\Tasks\TaskOptions;

class ReasonsForDisputeSection extends BaseSection implements SectionInterface, SectionSwitcherInterface
{
    /**
     * @var string[]
     */
    private array $switcherData;

    public function initializeTasks(TaskOptions $taskOptions): void
    {
        $tasks = [$this->getSwitcherTask()];

        $rfdTasks = $this->taskLocator->getTasksByRFD($this->switcherData);

        $this->tasks = array_merge($tasks, $rfdTasks);

        foreach ($this->tasks as $task) {
            $task->initialize($this->translator, $taskOptions);
        }
    }

    public function getSwitcherTask(): TaskInterface
    {
        return $this->taskLocator->getTaskById('reasons_for_dispute');
    }

    public function setSwitcherTaskData(mixed $data): void
    {
        if (is_array($data)) {
            $selected = array_filter($data);
            $this->switcherData = array_keys($selected);
        } else {
            $this->switcherData = [];
        }
    }

    public function getId(): string
    {
        return 'rfd';
    }

    public function getLabel(): string
    {
        return 'Details about the dispute ';
    }

    public function getReviewPageLabel(): string
    {
        return 'Details about the dispute ';
    }

    public function getSwitcherTaskData(): mixed
    {
        return implode(',', $this->switcherData);
    }

    public function getSwitcherTaskLabels(EmploymentDisputeDataPersister $storage, bool $showOtherText = true): ?string
    {
        $label = [];
        $task = $this->getSwitcherTask();
        assert($task instanceof ConfigFileTask);
        $formField = $task->getFormField();

        $employmentDisputeData = $storage->getEmploymentDispute()->getData();

        if (!empty($this->switcherData)) {
            foreach ($this->switcherData as $switcherData) {
                foreach ($formField as $key => $field) {
                    if ($key == $switcherData) {
                        if ('other' === $key && strlen($employmentDisputeData['sectionData']['rfd']['rfd']['other_text']) > 0 && $showOtherText) {
                            $label[] = $field['options']['label'].' ('.$employmentDisputeData['sectionData']['rfd']['rfd']['other_text'].')' ?? null;
                        } else {
                            $label[] = $field['options']['label'] ?? null;
                        }
                    }
                }
            }
        }
        $label = array_filter($label);

        return implode(', ', $label);
    }

    public function applyPayload(Payload $payload, EmploymentDisputeDataPersister $storage): void
    {
        $payload->addIncidentToken('reasons_for_dispute', $this->getSwitcherTaskLabels($storage));

        $task = $this->getTask('rfd', 't61_why_discrimated');
        $discriminationReasons = [];
        if ($task instanceof TaskInterface) {
            $discriminationReasons = $storage->getArrayTaskData($task)->getData();
        }

        $payload->setIncidentType($this->switcherData, $discriminationReasons);

        $total = 0;
        foreach ($this->getTasks() as $task) {
            if (OptionalStringTaskData::class === $task->getDataClass()) {
                $payload->addIncidentToken($task->getId(), $storage->getTaskStringData($task)->getData());
            }

            if (ExtraInformationTaskData::class === $task->getDataClass()) {
                $extraInfo = $storage->getExtraInformationData($task)->getData();
                // don't set it unless the user answered
                if ($extraInfo?->hasExtra()) {
                    // answer is 'yes'
                    $payload->addIncidentToken(
                        $task->getId(), [
                        'extra' => true,
                        'content' => $extraInfo->getExtraInformation(),
                        ]
                    );
                } elseif ($extraInfo) {
                    // answer is 'no'
                    $payload->addIncidentToken(
                        $task->getId(), [
                        'extra' => false,
                        'content' => '',
                        ]
                    );
                }
            }

            if (DateTaskData::class === $task->getDataClass()) {
                $value = [
                    'start' => $storage->getDateTaskData($task)->getStart()?->format('d-m-Y'),
                ];
                $payload->addIncidentToken($task->getId(), $value);
            }

            if (HolidayDateTaskData::class === $task->getDataClass()) {
                $value = [
                    'start' => $storage->getHolidayDateTaskData($task)->getStart()?->format('d-m-Y'),
                    'end' => $storage->getHolidayDateTaskData($task)->getEnd()?->format('d-m-Y'),
                ];
                $payload->addIncidentToken($task->getId(), $value);
            }

            if (MoneyTaskData::class === $task->getDataClass()) {
                $value = $storage->getMoneyTaskData($task)->getData();
                $payload->addIncidentToken($task->getId(), $value);
                $total += intval($value);
            }

            if (ArrayTaskData::class === $task->getDataClass()) {
                $data = $storage->getArrayTaskData($task)->getData();
                if (!empty($data)) {
                    $values = [];
                    if ('t79_why_is_it_about_whistleblowing' === $task->getId()) {
                        foreach ($data as $element) {
                            if (in_array($element, array_values(TaskOptions::CHOICE_IS_IT_ABOUT_WHISTLEBLOWING))) {
                                $text = array_search($element, TaskOptions::CHOICE_IS_IT_ABOUT_WHISTLEBLOWING);
                                if (is_string($text)) {
                                    $values[] = ucfirst($text);
                                }
                            }
                        }
                        $value = implode(', ', $values);
                    } elseif ('t61_why_discrimated' === $task->getId()) {
                        foreach ($data as $element) {
                            if (in_array($element, array_values(TaskOptions::CHOICE_WHY_DISCRIMINATED))) {
                                $text = array_search($element, TaskOptions::CHOICE_WHY_DISCRIMINATED);
                                if (is_string($text)) {
                                    $values[] = ucfirst($text);
                                }
                            }
                        }
                        $value = implode(', ', $values);
                    } elseif ('t77_raised_with' === $task->getId()) {
                        foreach ($data as $element) {
                            if (in_array($element, array_values(TaskOptions::CHOICE_RAISED_WITH))) {
                                $text = TaskOptions::REVIEW_DISPLAY_RAISED_WITH[$element] ?? '';
                                if (is_string($text)) {
                                    $values[] = ucfirst($text);
                                }
                            }
                        }
                        $value = implode(', ', $values);
                    } else {
                        $value = implode(', ', $storage->getArrayTaskData($task)->getData());
                    }
                    $payload->addIncidentToken($task->getId(), $value);
                }
            }

            if (ChoiceExtraTaskData::class === $task->getDataClass()) {
                $value = $storage->getChoiceExtraTaskData($task)->getData()?->getHasExtra();
                $payload->addIncidentToken($task->getId(), $value);
            }

            $this->setLessThanTwoYearsEmploymentFlag($task, $storage, $payload);

            $this->setProcessNotCompleteFlag($task, $storage, $payload);

            $this->setOutOfTimeFlag($task, $storage, $payload);

            $this->setOutOfTimeSixMonthsFlag($task, $storage, $payload);
        }

        $payload->setTotalOwed($total);
        $payload->addIncidentToken('total_owed', $total);
    }

    private function setProcessNotCompleteFlag(TaskInterface $task, EmploymentDisputeDataPersister $storage, Payload $payload): void
    {
        $internalProcedureQuestions = ['t54_internal_procedures', 't67_internal_procedures_dismissal', 't82_internal_procedures_whistleblowing'];
        if (in_array($task->getId(), $internalProcedureQuestions)) {
            $extraInfo = $storage->getExtraInformationData($task)->getData();
            if ($extraInfo && !$extraInfo->hasExtra()) {
                $payload->setProcessNotComplete(true);
            }
        }

        if ('t68_dismissal_appeal' === $task->getId() && 'no' === $storage->getTaskStringData($task)->getData()) {
            $payload->setProcessNotComplete(true);
        }
    }

    private function setLessThanTwoYearsEmploymentFlag(TaskInterface $task, EmploymentDisputeDataPersister $storage, Payload $payload): void
    {
        if ('t57_more_than_two_years' === $task->getId()) {
            $moreThanTwoYears = $storage->getChoiceExtraTaskData($task)?->getData();
            if ($moreThanTwoYears?->hasExtra()) {
                $payload->setLessThanTwoYearsEmployment(false);
            } elseif ($moreThanTwoYears) {
                $payload->setLessThanTwoYearsEmployment(true);
            }
        }
    }

    public function setOutOfTimeSixMonthsFlag(TaskInterface $task, EmploymentDisputeDataPersister $storage, Payload $payload, ?\DateTime $submissionDate = null): void
    {
        // From Grant: if date entered in: Q53b, is more than 6 months minus a day from submission date - last accepted day
        if ('t53b_when_redundancy_owed' === $task->getId()) {
            $owedDate = $storage->getDateTaskData($task)->getStart();
            if ($owedDate) {
                $this->setOutOfTimeRedundancy($owedDate, $payload, $submissionDate);
            }
        }
    }

    public function setOutOfTimeRedundancy(\DateTime $owedDate, Payload $payload, ?\DateTime $submissionDate = null): void
    {
        $submissionDate = $this->refineSubmissionDate($submissionDate);
        $sixMonthOutDate = $this->calculateOutOfTimeDate(6, $owedDate);

        if ($submissionDate >= $sixMonthOutDate) {
            $payload->setOutOfTimeRedundancy(true);
        } else {
            $payload->setOutOfTimeRedundancy(false);
        }
    }

    private function setOutOfTimeFlag(TaskInterface $task, EmploymentDisputeDataPersister $storage, Payload $payload): void
    {
        // From Grant: if date entered in: Q53  Q58 Q62 Q69 Q81 Q84, is more than 3 months minus a day from submission date - last accepted day
        switch ($task->getId()) {
            case 't53_when_wages_owed':
            case 't58_termination_date':
            case 't62_when_discriminated':
            case 't69_resignation_date':
                $outOfTimeDate = $storage->getDateTaskData($task)->getStart();
                $this->setOutOfTime($outOfTimeDate, $payload); // any of the dates out of time will set the flag to out of time
                break;
            case 't81_date_of_detriment':
            case 't84_dispute_date':
                $extraInfo = $storage->getExtraInformationData($task)->getData();
                // don't set it unless the user answered
                if ($extraInfo?->hasExtra() && $extraInfo?->getExtraInformation()) {
                    // answer is 'yes'
                    $outOfTimeDateExtraDate = $extraInfo->getExtraInformation();
                    $this->setOutOfTime($outOfTimeDateExtraDate, $payload);
                }
                break;
        }
    }

    public function setOutOfTime(?\DateTime $incidentDate, Payload $payload, ?\DateTime $submissionDate = null): void
    {
        if ($incidentDate) {
            $submissionDate = $this->refineSubmissionDate($submissionDate);
            $threeMonthOutDate = $this->calculateOutOfTimeDate(3, $incidentDate);

            if ($submissionDate >= $threeMonthOutDate) {
                $payload->setOutOfTime(true);
            }
        }
    }

    private function refineSubmissionDate(?\DateTime $submissionDate): \DateTime
    {
        // @todo review the submission date implementation -
        // we are not passing the first submission date right now which might be incorrect
        if ($submissionDate) {
            $submissionDate->setTimezone(new \DateTimeZone('UTC'));
        } else {
            $submissionDate = new \DateTime('now', new \DateTimeZone('UTC'));
        }
        $submissionDate->setTime(23, 59, 59);

        return $submissionDate;
    }

    private function calculateOutOfTimeDate(int $numberOfMonths, \DateTime $incidentDate)
    {
        // example dates and results in the PayloadTest.php testOutOfTimeFlag's dataProvider
        $incidentDateString = $incidentDate->format('Y-n-d');
        $incidentDateArray = explode('-', $incidentDateString);
        if (!$incidentDateArray) {
            return;
        }

        [$year, $month, $day] = $incidentDateArray;

        $inNumberOfMonthTime = (int) $month + $numberOfMonths;

        if ($inNumberOfMonthTime > 12) {
            $inNumberOfMonthTime = $inNumberOfMonthTime % 12;
            ++$year;
        }

        $isValidDate = checkdate($inNumberOfMonthTime, $day, $year);

        $outOfDate = clone $incidentDate;
        if ($isValidDate) {
            $outOfDate->setDate($year, $inNumberOfMonthTime, $day);
        } else {
            $outOfDate->setDate($year, $inNumberOfMonthTime + 1, 1);
        }

        $outOfDate->setTimezone(new \DateTimeZone('UTC'));
        $outOfDate->setTime(23, 59, 59);

        return $outOfDate;
    }
}
