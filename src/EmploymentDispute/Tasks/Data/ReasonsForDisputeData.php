<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

class ReasonsForDisputeData implements TaskDataInterface
{
    private bool $wages = false;
    private bool $holiday = false;
    private bool $redundancy = false;
    private bool $discrimination = false;
    private bool $unfair_dismissal = false;
    private bool $constructive_dismissal = false;
    private bool $wrongful_dismissal = false;
    private bool $equal_pay = false;
    private bool $whistleblowing = false;
    private bool $other = false;
    private ?string $other_text = null;

    public function isEmpty(): bool
    {
        if ($this->wages ||
            $this->holiday ||
            $this->redundancy ||
            $this->discrimination ||
            $this->unfair_dismissal ||
            $this->constructive_dismissal ||
            $this->wrongful_dismissal ||
            $this->equal_pay ||
            $this->whistleblowing ||
            $this->other
        ) {
            return false;
        } else {
            return true;
        }
    }

    public function getWages(): bool
    {
        return $this->wages;
    }

    public function setWages(bool $wages): self
    {
        $this->wages = $wages;

        return $this;
    }

    public function getHoliday(): bool
    {
        return $this->holiday;
    }

    public function setHoliday(bool $holiday): self
    {
        $this->holiday = $holiday;

        return $this;
    }

    public function getRedundancy(): bool
    {
        return $this->redundancy;
    }

    public function setRedundancy(bool $redundancy): self
    {
        $this->redundancy = $redundancy;

        return $this;
    }

    public function getDiscrimination(): bool
    {
        return $this->discrimination;
    }

    public function setDiscrimination(bool $discrimination): self
    {
        $this->discrimination = $discrimination;

        return $this;
    }

    #[Ignore]
    public function getUnfairDismissal(): bool
    {
        return $this->unfair_dismissal;
    }

    public function setUnfairDismissal(bool $unfair_dismissal): self
    {
        $this->unfair_dismissal = $unfair_dismissal;

        return $this;
    }

    #[Ignore]
    public function getConstructiveDismissal(): bool
    {
        return $this->constructive_dismissal;
    }

    #[Ignore]
    public function setConstructiveDismissal(bool $constructive_dismissal): self
    {
        $this->constructive_dismissal = $constructive_dismissal;

        return $this;
    }

    #[Ignore]
    public function getWrongfulDismissal(): bool
    {
        return $this->wrongful_dismissal;
    }

    public function setWrongfulDismissal(bool $wrongful_dismissal): self
    {
        $this->wrongful_dismissal = $wrongful_dismissal;

        return $this;
    }

    #[Ignore]
    public function getEqualPay(): bool
    {
        return $this->equal_pay;
    }

    public function setEqualPay(bool $equal_pay): self
    {
        $this->equal_pay = $equal_pay;

        return $this;
    }

    public function getWhistleblowing(): bool
    {
        return $this->whistleblowing;
    }

    public function setWhistleblowing(bool $whistleblowing): self
    {
        $this->whistleblowing = $whistleblowing;

        return $this;
    }

    public function getOther(): bool
    {
        return $this->other;
    }

    public function setOther(bool $other): self
    {
        $this->other = $other;

        return $this;
    }

    #[Ignore]
    public function getOtherText(): ?string
    {
        return $this->other_text;
    }

    public function setOtherText(?string $other_text): self
    {
        $this->other_text = $other_text;

        return $this;
    }
}
