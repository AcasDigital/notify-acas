<?php

namespace App\EmploymentDispute\Sections;

use App\EmploymentDispute\Submission\Payload;
use App\EmploymentDispute\TaskList\EmploymentDisputeDataPersister;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Translation\TranslatorService;

class RepresentativeSection extends BaseSection implements SectionInterface
{
    public const TASK_NAME = 'representative_name_optional';
    public const TASK_CONTACT_DETAILS = 'representative_contact_details';
    public const TASK_EMAIL = 'representative_email_optional';
    public const TASK_PHONE = 'representative_phone';
    public const TASK_ADDRESS = 'representative_address';

    public function initializeTasks(TaskOptions $taskOptions): void
    {
        $taskIds = [];
        if (TaskOptions::FLOW_EARLY_CONCILIATION === $taskOptions->getFlow()) {
            if (TaskOptions::REPRESENTATIVE_MYSELF === $taskOptions->getRepresentative()) {
                $taskIds = [
                    self::TASK_NAME,
                    self::TASK_CONTACT_DETAILS,
                ];
            } else {
                if ($taskOptions->hasPhone() && $taskOptions->hasEmail()) {
                    // covers phone-email
                    $taskIds = [
                        self::TASK_NAME,
                        self::TASK_EMAIL,
                        self::TASK_PHONE,
                    ];
                } elseif ($taskOptions->hasPhone()) {
                    // covers phone-post
                    $taskIds = [
                        self::TASK_NAME,
                        self::TASK_PHONE,
                        self::TASK_ADDRESS,
                    ];
                } elseif ($taskOptions->hasPost()) {
                    // covers post
                    $taskIds = [
                        self::TASK_NAME,
                        self::TASK_ADDRESS,
                    ];
                } else {
                    // covers email
                    $taskIds = [
                        self::TASK_NAME,
                        self::TASK_EMAIL,
                    ];
                }
            }
        } else {
            if ($taskOptions->hasPost()) {
                // covers post
                $taskIds = [
                    self::TASK_NAME,
                    self::TASK_ADDRESS,
                ];
            } else {
                // covers email
                $taskIds = [
                    self::TASK_NAME,
                    self::TASK_EMAIL,
                ];
            }
        }

        foreach ($taskIds as $id) {
            $task = $this->taskLocator->getTaskById($id);
            $task->initialize($this->translator, $taskOptions);
            $this->tasks[$id] = $task;
        }
    }

    public function initializeTranslator(TranslatorService $translator): void
    {
        $this->translator = $translator;
    }

    public function initializeTaskOptions(TaskOptions $taskOptions): void
    {
        $this->taskOptions = $taskOptions;
    }

    public function getId(): string
    {
        return 'representative';
    }

    public function getLabel(): string
    {
        if (TaskOptions::FLOW_EARLY_CONCILIATION === $this->taskOptions->getFlow()) {
            if (TaskOptions::REPRESENTATIVE_MYSELF === $this->taskOptions->getRepresentative()) {
                return "Enter your representative's details, if you have one";
            } else {
                return 'Your details';
            }
        } else {
            // certification journey
            if (TaskOptions::REPRESENTATIVE_MYSELF === $this->taskOptions->getRepresentative()) {
                return "Enter your representative's details, if you have one"; // the section in this scenario doesn't get displayed
            } else {
                return 'Your details';
            }
        }
    }

    public function getReviewPageLabel(): string
    {
        return TaskOptions::REPRESENTATIVE_MYSELF === $this->taskOptions->getRepresentative() ? 'Representative details' : 'Your details';
    }

    public function applyPayload(Payload $payload, EmploymentDisputeDataPersister $storage): void
    {
        $tasks = $this->getTasks();
        if (isset($tasks[self::TASK_NAME])) {
            $fullName = $storage->getTaskFullNameData($tasks[self::TASK_NAME])->getFullName();
            $payload->setRepresentativeName($fullName);

            if ($fullName) {
                $name = $fullName->getFirstName().' '.$fullName->getLastName();
                $payload->addRepresentativeDetail($name);
            }
        }

        if (isset($tasks[self::TASK_EMAIL])) {
            $email = $storage->getTaskStringData($tasks[self::TASK_EMAIL])->getData();
            $payload->setRepresentativeEmail($email);

            if ($email) {
                $payload->addRepresentativeDetail($email);
            }
        }

        if (isset($tasks[self::TASK_PHONE])) {
            $phone = $storage->getTaskPhoneWithoutVerificationData($tasks[self::TASK_PHONE])->getPhone();
            $payload->setRepresentativePhoneNumber($phone?->getPhoneNumber());
            $payload->setRepresentativeAlternativePhoneNumber($phone?->getAlternativeNumber());

            if ($phone?->getExtraInformation()) {
                $payload->setDescription($phone->getExtraInformation());
            }

            $phoneDetails = [];
            if ($phone) {
                if ($phone->getPhoneNumber()) {
                    $phoneDetails[] = $phone->getPhoneNumber();
                }
                if ($phone->getAlternativeNumber()) {
                    $phoneDetails[] = $phone->getAlternativeNumber();
                }
                if ($phone->getExtraInformation()) {
                    $phoneDetails[] = $phone->getExtraInformation();
                }
                if (!empty($phoneDetails)) {
                    $payload->addRepresentativeDetail(implode(PHP_EOL, $phoneDetails));
                }
            }
        }

        if (isset($tasks[self::TASK_ADDRESS])) {
            $address = $storage->getOptionalTaskAddressData($tasks[self::TASK_ADDRESS]);
            $payload->setRepresentativeAddress($address->getAddress());

            $addressDetails = [];

            if ($address->getAddress()?->getAddressFirstLine()) {
                $addressDetails[] = $address->getAddress()?->getAddressFirstLine();
            }
            if ($address->getAddress()?->getAddressSecondLine()) {
                $addressDetails[] = $address->getAddress()?->getAddressSecondLine();
            }
            if ($address->getAddress()?->getTown()) {
                $addressDetails[] = $address->getAddress()?->getTown();
            }
            if ($address->getAddress()?->getPostcode()) {
                $addressDetails[] = $address->getAddress()?->getPostcode();
            }

            if (!empty($addressDetails)) {
                $payload->addRepresentativeDetail(implode(PHP_EOL, $addressDetails));
            }
        }

        if (isset($tasks[self::TASK_CONTACT_DETAILS])) {
            $contact = $storage->getTaskContactDetailsData($tasks[self::TASK_CONTACT_DETAILS]);
            $payload->setRepresentativeEmail($contact->getEmail());
            $payload->setRepresentativePhoneNumber($contact->getPhone());

            $contactDetails = [];

            if ($contact->getEmail()) {
                $contactDetails[] = $contact->getEmail();
            }
            if ($contact->getPhone()) {
                $contactDetails[] = $contact->getPhone();
            }

            if (!empty($contactDetails)) {
                $payload->addRepresentativeDetail(implode(PHP_EOL, $contactDetails));
            }
        }
    }
}
