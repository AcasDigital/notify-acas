<?php

namespace App\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;

class PhoneData
{
    #[Assert\Length(max: 50)]
    private ?string $phoneNumber = null;
    private ?bool $mobileConfirmation = null;
    #[Assert\Length(max: 50)]
    private ?string $alternativeNumber = null;
    private ?string $extraInformation = null;

    public function isEmpty(): bool
    {
        return empty($this->getPhoneNumber());
    }

    public function getExtraInformation(): ?string
    {
        return $this->extraInformation;
    }

    public function setExtraInformation(?string $extraInformation): self
    {
        $this->extraInformation = $extraInformation;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = preg_replace('/\s+/', '', $phoneNumber);

        return $this;
    }

    public function getMobileConfirmation(): ?bool
    {
        return $this->mobileConfirmation;
    }

    public function setMobileConfirmation(?bool $mobileConfirmation): self
    {
        $this->mobileConfirmation = $mobileConfirmation;

        return $this;
    }

    public function getAlternativeNumber(): ?string
    {
        return $this->alternativeNumber;
    }

    public function setAlternativeNumber(?string $alternativeNumber): self
    {
        $this->alternativeNumber = $alternativeNumber;

        return $this;
    }
}
