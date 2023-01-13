<?php

namespace App\EmploymentDispute\TaskList;

use App\EmploymentDispute\Tasks\Data\PhoneWithoutVerificationTaskData;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\EmploymentDispute\Wizard\WizardData;
use App\EmploymentDispute\Wizard\WizardDataPersister;
use App\Entity\EmploymentDispute;
use App\Form\Data\PhoneWithoutVerificationData;
use App\Repository\EmploymentDisputeRepository;
use Psr\Log\LoggerInterface;

class EmploymentDisputeCreator
{
    /**
     * @var array<string>
     */
    private array $allowedChars = [
        'ABCDEFGHJKLMNPQRSTUVWXYZ', // no I, O
        '23456789', // NO 1, 0
    ];

    public function __construct(
        private EmploymentDisputeRepository $disputeFormRepository,
        private EmploymentDisputeDataPersister $persister,
        private LoggerInterface $logger,
    ) {
    }

    public function createFromWizard(WizardData $data, WizardDataPersister $wizardDataPersister): EmploymentDispute
    {
        $form = new EmploymentDispute();

        $taskData = $wizardDataPersister->getTaskStringData('wizard_early_conciliation')->getData();
        $type = 'yes' === $taskData ? TaskOptions::FLOW_EARLY_CONCILIATION : TaskOptions::FLOW_CERTIFICATE;
        $form->setType($type);

        $taskData = $wizardDataPersister->getTaskStringData('wizard_representing')->getData() ?? '';
        $form->setRepresenting($taskData);

        if (TaskOptions::FLOW_EARLY_CONCILIATION === $type) {
            $contactMethod = $wizardDataPersister->getTaskStringData('wizard_contact_early_conciliation')->getData();
        } else {
            $contactMethod = $wizardDataPersister->getTaskStringData('wizard_contact_certificate')->getData();
        }
        $form->setContactMethod($contactMethod);

        $verificationContact = null;
        if (in_array($contactMethod, [TaskOptions::CONTACT_METHOD_EMAIL, TaskOptions::CONTACT_METHOD_PHONE_EMAIL])) {
            $verificationContact = $wizardDataPersister->getOptionalTaskEmailData('wizard_email')->getData();
        } elseif (in_array($contactMethod, [TaskOptions::CONTACT_METHOD_PHONE_POST])) {
            $isVerified = $wizardDataPersister->getOptionalPhoneData('wizard_phone')->getPhone()?->getMobileConfirmation() ?: false;
            if ($isVerified) {
                $verificationContact = $wizardDataPersister->getOptionalPhoneData('wizard_phone')->getPhone()?->getPhoneNumber();
            }
        }
        $form->setVerificationContact($verificationContact);

        $form->setStatus(EmploymentDispute::STATUS_DRAFT);
        $memorableWord = $wizardDataPersister->getMemorableWordData('wizard_memorable_word')->getData() ?? '';
        $form->setMemorableWord($memorableWord);
        $SRNumber = $this->generateSaveAndReturnNumber();
        $form->setId($SRNumber);

        $form->setCreated(new \DateTime());
        $form->setModified(new \DateTime());

        $this->disputeFormRepository->add($form);

        $this->prefillData($form, $wizardDataPersister);

        return $form;
    }

    private function prefillData(EmploymentDispute $dispute, WizardDataPersister $wizardDataPersister): void
    {
        // get email, phone and address details from the wizard
        $this->persister->setEmploymentDispute($dispute);

        // Email and phone details are pre-filled based on the response in the representing screen.
        // We are also checking against the final contact method in case the user filled out a contact mode
        // then changes their mind during verification.
        $representing = $wizardDataPersister->getTaskStringData('wizard_representing')->getData();

        if (in_array($dispute->getContactMethod(), [TaskOptions::CONTACT_METHOD_POST, TaskOptions::CONTACT_METHOD_PHONE_POST])) {
            if (TaskOptions::REPRESENTATIVE_MYSELF === $representing) {
                $addressData = $wizardDataPersister->getTaskAddressData('wizard_address');
                $this->persister->setTaskData('claimant', 'claimant_address', $addressData);
            } else {
                $addressData = $wizardDataPersister->getOptionalTaskAddressData('wizard_address_optional');
                $this->persister->setTaskData('representative', 'representative_address', $addressData);
            }
        }

        if (TaskOptions::REPRESENTATIVE_MYSELF === $representing) {
            $sectionId = 'claimant';
            $emailTaskId = 'claimant_email';
            $phoneTaskId = 'claimant_phone';
        } else {
            $sectionId = 'representative';
            $emailTaskId = 'representative_email';
            $phoneTaskId = 'representative_phone';
        }

        if (in_array($dispute->getContactMethod(), [TaskOptions::CONTACT_METHOD_EMAIL, TaskOptions::CONTACT_METHOD_PHONE_EMAIL])) {
            $emailData = $addressData = $wizardDataPersister->getOptionalTaskEmailData('wizard_email');
            $this->persister->setTaskData($sectionId, $emailTaskId, $emailData);
        }

        if (in_array($dispute->getContactMethod(), [TaskOptions::CONTACT_METHOD_PHONE_POST])) {
            $phoneWizardData = $addressData = $wizardDataPersister->getOptionalPhoneData('wizard_phone');

            $phoneFormData = new PhoneWithoutVerificationData();
            $phoneFormData->setPhoneNumber($phoneWizardData->getPhone()?->getPhoneNumber());
            $phoneFormData->setAlternativeNumber($phoneWizardData->getPhone()?->getAlternativeNumber());
            $phoneFormData->setExtraInformation($phoneWizardData->getPhone()?->getExtraInformation());
            // Check if phone number has gone through verification
            if ($phoneWizardData->getPhone()?->getMobileConfirmation()) {
                $phoneFormData->setIsVerified(true);
            }

            $phoneFormTaskData = new PhoneWithoutVerificationTaskData();
            $phoneFormTaskData->setPhone($phoneFormData);

            $this->persister->setTaskData($sectionId, $phoneTaskId, $phoneFormTaskData);
        }

        if (in_array($dispute->getContactMethod(), [TaskOptions::CONTACT_METHOD_PHONE_EMAIL])) {
            $phoneWizardData = $addressData = $wizardDataPersister->getPhoneWithoutVerificationPhoneData('wizard_phone_without_verification');
            $this->persister->setTaskData($sectionId, $phoneTaskId, $phoneWizardData);
        }
    }

    private function generateSaveAndReturnNumber(): string
    {
        $SRNumber = $this->randomString(4, implode($this->allowedChars)).'-'.$this->randomString(4, implode($this->allowedChars));

        // Check in the db if the generated number is unique
        $disputeForm = $this->disputeFormRepository->find($SRNumber);
        if ($disputeForm) {
            $this->logger->info('[EMPLOYMENTDISPUTECREATOR] Re-generating Save and return number');

            return self::generateSaveAndReturnNumber();
        } else {
            return $SRNumber;
        }
    }

    /**
     * Generate a random string of a chosen length and chosen character list.
     *
     * credit: https://stackoverflow.com/questions/4356289/php-random-string-generator/31107425#31107425
     *
     * @param int    $length   How many characters do we want?
     * @param string $charList A string of all possible characters
     *                         to select from
     */
    private function randomString(int $length, string $charList): string
    {
        if ($length < 1) {
            throw new \RangeException('Length must be a positive integer');
        }

        $string = '';
        $max = mb_strlen($charList, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $string .= $charList[random_int(0, $max)];
        }

        return $string;
    }
}
