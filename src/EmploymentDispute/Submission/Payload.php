<?php

namespace App\EmploymentDispute\Submission;

use App\EmploymentDispute\Tasks\Data\OrgNameTaskData;
use App\EmploymentDispute\Tasks\TaskOptions;
use App\Entity\CaseNumber;
use App\Form\Data\AddressData;
use App\Form\Data\FullNameData;
use App\Form\Data\OptionalAddressData;
use App\Form\Data\PhoneWithoutVerificationData;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\SerializedName;

class Payload
{
    public const RESPONDENT_PERSON = '602700001';
    public const RESPONDENT_ORG = '602700000';

    public const CONTACT_PHONE_EMAIL = '602700000';
    public const CONTACT_EMAIL = '602700001';
    public const CONTACT_POST_PHONE = '602700002';
    public const CONTACT_POST = '602700003';

    public const JURISDICTION_WA = '602700000';
    public const JURISDICTION_WTR = '602700001';
    public const JURISDICTION_UDL = '602700002';
    public const JURISDICTION_RPT = '602700006';
    public const JURISDICTION_DAG = '602700007';
    public const JURISDICTION_DDA = '602700008';
    public const JURISDICTION_SXD = '602700009';
    public const JURISDICTION_MAT = '602700010';
    public const JURISDICTION_DRB = '602700011';
    public const JURISDICTION_RRD = '602700012';
    public const JURISDICTION_DSO = '602700013';
    public const JURISDICTION_BOC = '602700014';
    public const JURISDICTION_EQP = '602700015';
    public const JURISDICTION_PID = '602700016';

    public const JURISDICTION_DISCRIMINATION = '602700003';
    public const JURISDICTION_OTHER = '602700005';

    #[SerializedName('acas_modeoffirstcontact')]
    public ?string $contactMethod;

    #[SerializedName('claimantfirstname')]
    public ?string $claimantFirstName;
    #[SerializedName('claimantnormalisedfirstname')]
    public ?string $claimantNormalisedFirstName;
    #[SerializedName('claimantsurname')]
    public ?string $claimantLastName;

    #[SerializedName('claimantmainphoneno')]
    public ?string $claimantPhoneNumber;
    #[SerializedName('claimantnormalisedmainphoneno')]
    public ?string $claimantNormalisedPhoneNumber;
    #[SerializedName('claimantalternativephoneno')]
    public ?string $claimantAlternativePhoneNumber;

    #[SerializedName('claimantemailaddress')]
    public ?string $claimantEmail;

    #[SerializedName('claimantaddressline1')]
    public ?string $claimantAddressLineOne;
    #[SerializedName('claimantaddressline2')]
    public ?string $claimantAddressLineTwo;
    #[SerializedName('claimantpostcode')]
    public ?string $claimantPostcode;
    #[SerializedName('claimantnormalisedpostcode')]
    public ?string $claimantNormalisedPostcode;
    #[SerializedName('claimantcitytown')]
    public ?string $claimantTown;

    #[SerializedName('claimrepfirstname')]
    public ?string $representativeFirstName;
    #[SerializedName('claimantrepnormalisedfirstname')]
    public ?string $representativeNormalisedFirstName;
    #[SerializedName('claimrepsurname')]
    public ?string $representativeLastName;

    #[SerializedName('claimrepmainphoneno')]
    public ?string $representativePhoneNumber;
    #[SerializedName('claimantrepnormalisedmainphoneno')]
    public ?string $representativeNormalisedPhoneNumber;
    #[SerializedName('claimrepalternativephoneno')]
    public ?string $representativeAlternativePhoneNumber;

    #[SerializedName('claimrepemailaddress')]
    public ?string $representativeEmail;

    #[SerializedName('claimrepaddressline1')]
    public ?string $representativeAddressLine1;
    #[SerializedName('claimrepaddressline2')]
    public ?string $representativeAddressLine2;
    #[SerializedName('claimrepcitytown')]
    public ?string $representativeCity;
    #[SerializedName('claimreppostcode')]
    public ?string $representativePostcode;
    #[SerializedName('claimantrepnormalisedpostcode')]
    public ?string $representativeNormalisedPostcode;

    /**
     * @var array<array<string, string|null>>
     */
    #[SerializedName('acas_respondent_records')]
    public array $respondents;

    #[SerializedName('acas_incidentinfo')]
    public string $incidentInfo;

    #[SerializedName('acas_incidenttype')]
    public string $incidentType;

    #[SerializedName('acas_monetaryvalue')]
    public int $totalOwed = 0;

    #[SerializedName('acas_flagnoconciliation')]
    public bool $noConciliation;

    #[SerializedName('acas_dateofreceipt')]
    public string $dateOfReciept;

    #[SerializedName('acas_dateandtimeofsubmission')]
    public string $dateOfSubmission;

    #[SerializedName('acas_flaginternalprocessnotcomplete')]
    public bool $processNotComplete;

    #[SerializedName('acas_flagoutoftime')]
    public bool $outOfTime = false;

    #[SerializedName('acas_flagoutoftime6month')]
    public bool $outOfTime6Months = false;

    #[SerializedName('acas_flaglessthan2yearsemployment')]
    public bool $lessThan2YearsEmployment = false;

    #[SerializedName('acas_flaginsolvency')]
    public bool $insolvency = false;

    #[SerializedName('acas_sendallocationemail')]
    public bool $allocationEmail = false;

    // Group claim related
    #[SerializedName('acas_flagoneofseveralemployees')]
    public bool $oneOfServeralEmployees = false;

    // Group claim related
    #[SerializedName('acas_requireslinking')]
    public bool $requiresLinking;

    #[SerializedName('acas_relationtoclaimant')]
    public string $relationship = '';

    #[SerializedName('description')]
    public string $description;

    // Concatenated representative contact details
    #[SerializedName('acas_internalgrievancehistory')]
    public string $internalGrievanceHistory;

    #[SerializedName('acas_accessibilityneeds')]
    public string $accessibilityNeeds;

    /**
     * @var array<string>
     */
    private array $representativeDetails;

    /**
     * @var array<string, mixed>
     */
    private array $incidentTokens;

    public function __construct(private PayloadNormaliser $payloadNormaliser)
    {
    }

    public function addOrganisationInfo(OrgNameTaskData $organisation, bool $insolvent): void
    {
        $this->incidentTokens['org_info'][] = [
            'name' => $organisation->getOrgName(),
            'not_trading' => $insolvent,
            'currently_employed' => $organisation->getStillWorkingForOrg(),
        ];
    }

    public function setDateTime(\DateTimeInterface $dateTime): void
    {
        $dateTime = \DateTime::createFromInterface($dateTime);
        $this->dateOfReciept = $dateTime->format('Y-m-d');
        $this->dateOfSubmission = $dateTime->format('Y-m-d\TH:i:s\Z');
    }

    /**
     * @param Collection<CaseNumber> $caseNumbers
     */
    public function setCaseNumbers($caseNumbers): void
    {
        $updated = [];
        $caseNumbersArray = $caseNumbers->toArray();

        if (!empty($caseNumbersArray) && count($this->respondents) !== count($caseNumbersArray)) {
            throw new \Exception('Case number count doesn\'nt match with respondents.');
        }

        foreach ($this->respondents as $key => $respondent) {
            $respondent['acas_eccaserefno'] = !empty($caseNumbersArray) ? $caseNumbersArray[$key]->getCaseRefNumber() : null;
            $updated[] = $respondent;
        }

        $this->respondents = $updated;
    }

    public function addIncidentToken(string $key, mixed $value): void
    {
        $this->incidentTokens[$key] = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveIncidentTokens(): array
    {
        return array_filter($this->incidentTokens, function ($value) {
            return !empty($value);
        });
    }

    /**
     * @param string[] $types
     * @param string[] $extraReasons
     */
    public function setIncidentType(array $types, array $extraReasons): void
    {
        if (empty($types)) {
            return;
        }

        $mappedType = [];
        foreach ($types as $type) {
            // Only set discrimination jurisdication if the user has not selected
            // the type of discrimination - these are passed as $extraReasons.
            if ('discrimination' == $type && empty($extraReasons)) {
                $mappedType[] = self::JURISDICTION_DISCRIMINATION;
                continue;
            }

            $mappedType[] = match ($type) {
                'wages' => self::JURISDICTION_WA,
                'holiday' => self::JURISDICTION_WTR,
                'discrimination' => null,
                'unfair_dismissal' => self::JURISDICTION_UDL,
                'wrongful_dismissal' => self::JURISDICTION_BOC,
                'constructive_dismissal' => self::JURISDICTION_UDL,
                'redundancy' => self::JURISDICTION_RPT,
                'equal_pay' => self::JURISDICTION_EQP,
                'whistleblowing' => self::JURISDICTION_PID,
                default => self::JURISDICTION_OTHER,
            };
        }

        foreach ($extraReasons as $reason) {
            $mappedType[] = match ($reason) {
                'age' => self::JURISDICTION_DAG,
                'disability' => self::JURISDICTION_DDA,
                'gender_reassignment' => self::JURISDICTION_SXD,
                'marriage' => self::JURISDICTION_SXD,
                'maternity' => sprintf('%s,%s', self::JURISDICTION_MAT, self::JURISDICTION_SXD),
                'religion' => self::JURISDICTION_DRB,
                'race' => self::JURISDICTION_RRD,
                'sex' => self::JURISDICTION_SXD,
                'sexual_orientation' => self::JURISDICTION_DSO,
                default => null,
            };
        }

        $mappedType = array_filter($mappedType);

        if ($mappedType) {
            $this->incidentType = implode(',', array_unique($mappedType));
        }
    }

    public function setType(?string $type): void
    {
        $this->noConciliation = TaskOptions::FLOW_CERTIFICATE === $type;
    }

    public function setTotalOwed(int $money): void
    {
        $this->totalOwed = $money;
    }

    public function setIncidentInfo(string $incidentInfo): void
    {
        $this->incidentInfo = $incidentInfo;
    }

    public function addContactMethod(?string $contactMethod): void
    {
        $this->addIncidentToken('contact_method', $contactMethod);
        $this->contactMethod = match ($contactMethod) {
            'post' => self::CONTACT_POST,
            'post-phone' => self::CONTACT_POST_PHONE,
            'email' => self::CONTACT_EMAIL,
            'phone-email' => self::CONTACT_PHONE_EMAIL,
            default => self::CONTACT_POST,
        };
    }

    public function setClaimantName(?FullNameData $fullNameData): void
    {
        $this->claimantFirstName = $fullNameData?->getFirstName();
        $this->claimantNormalisedFirstName = $this->payloadNormaliser->normaliseFirstName($fullNameData?->getFirstName());
        $this->claimantLastName = $fullNameData?->getLastName();
    }

    public function setRepresentativeName(?FullNameData $fullNameData): void
    {
        $this->representativeFirstName = $fullNameData?->getFirstName();
        $this->representativeNormalisedFirstName = $this->payloadNormaliser->normaliseFirstName($fullNameData?->getFirstName());
        $this->representativeLastName = $fullNameData?->getLastName();
    }

    public function setClaimantPhoneNumber(?PhoneWithoutVerificationData $phoneData): void
    {
        $this->claimantPhoneNumber = $phoneData?->getPhoneNumber();
        $this->claimantNormalisedPhoneNumber = $this->payloadNormaliser->normalisePhone($phoneData?->getPhoneNumber());
        $this->claimantAlternativePhoneNumber = $phoneData?->getAlternativeNumber();
    }

    public function setRepresentativePhoneNumber(?string $phone): void
    {
        $this->representativePhoneNumber = $phone;
        $this->representativeNormalisedPhoneNumber = $this->payloadNormaliser->normalisePhone($phone);
    }

    public function setRepresentativeAlternativePhoneNumber(?string $phone): void
    {
        $this->representativeAlternativePhoneNumber = $phone;
    }

    public function setRepresentativeEmail(?string $email): void
    {
        $this->representativeEmail = $email;
    }

    public function setRepresentativeAddress(?OptionalAddressData $address): void
    {
        $this->representativeAddressLine1 = $address?->getAddressFirstLine() ?: '';
        $this->representativeAddressLine2 = $address?->getAddressSecondLine() ?: '';
        $this->representativePostcode = $address?->getPostcode() ?: '';
        $this->representativeNormalisedPostcode = $this->payloadNormaliser->normalisePostCode($address?->getPostcode() ?: '');
        $this->representativeCity = $address?->getTown() ?: '';
    }

    public function setClaimantAddress(?AddressData $address): void
    {
        $this->claimantAddressLineOne = $address?->getAddressFirstLine();
        $this->claimantAddressLineTwo = $address?->getAddressSecondLine() ?: '';
        $this->claimantPostcode = $address?->getPostcode() ?: '';
        $this->claimantNormalisedPostcode = $this->payloadNormaliser->normalisePostCode($address?->getPostcode() ?: '');
        $this->claimantTown = $address?->getTown();
    }

    public function setClaimantEmail(?string $email): void
    {
        $this->claimantEmail = $email;
    }

    public function addRespondentPerson(?FullNameData $fullName, ?AddressData $address): void
    {
        $this->respondents[] = [
            'acas_respondenttype' => self::RESPONDENT_PERSON,
            'firstname' => $fullName?->getFirstName(),
            'respondentnormalisedfirstname' => $this->payloadNormaliser->normaliseFirstName($fullName?->getFirstName() ?: ''),
            'lastname' => $fullName?->getLastName(),
            'address1_line1' => $address?->getAddressFirstLine(),
            'address1_line2' => $address?->getAddressSecondLine() ?: '', // not mandatory
            'address1_postalcode' => $address?->getPostcode() ?: '', // not mandatory
            'respondentnormalisedpostcode' => $this->payloadNormaliser->normalisePostCode($address?->getPostcode() ?: ''),
            'address1_city' => $address?->getTown(),
        ];
    }

    public function addRespondentOrganisation(?string $orgName, ?AddressData $address): void
    {
        $this->respondents[] = [
            'acas_respondenttype' => self::RESPONDENT_ORG,
            'name' => $orgName,
            'respondentnormalisedname' => $this->payloadNormaliser->normaliseOrganisation($orgName),
            'address1_line1' => $address?->getAddressFirstLine(),
            'address1_line2' => $address?->getAddressSecondLine() ?: '', // not mandatory
            'address1_postalcode' => $address?->getPostcode() ?: '', // not mandatory
            'respondentnormalisedpostcode' => $this->payloadNormaliser->normalisePostCode($address?->getPostcode() ?: ''),
            'address1_city' => $address?->getTown(),
        ];
    }

    public function setRelationshipToClaimant(string $relationship): void
    {
        $this->relationship = $relationship;
    }

    public function setOutOfTimeRedundancy(bool $flag): void
    {
        $this->outOfTime6Months = $flag;
    }

    public function setOutOfTime(bool $flag): void
    {
        $this->outOfTime = $flag;
    }

    public function setOrganisationOutOfBusiness(bool $flag): void
    {
        $this->insolvency = $flag;
    }

    public function setLessThanTwoYearsEmployment(bool $flag): void
    {
        $this->lessThan2YearsEmployment = $flag;
    }

    /**
     * Set the value of description to display it in the Case summary field.
     */
    public function setDescription(?string $description): void
    {
        $this->description = 'Accessibility needs: '.$description;
        $this->accessibilityNeeds = $description;
    }

    public function setProcessNotComplete(bool $processNotComplete): void
    {
        $this->processNotComplete = $processNotComplete;
    }

    /**
     * The concatenated representative details
     * It should be sent to the API in case the the CRM could not create
     * the representative contact card due to missing details.
     */
    public function addRepresentativeDetail(string $detail): void
    {
        $this->representativeDetails[] = $detail;

        $this->internalGrievanceHistory = implode(PHP_EOL, $this->representativeDetails);
    }
}
