<?php

namespace App\Form\Data;

use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class PhoneWithoutVerificationData
{
    #[Assert\Length(max: 50)]
    private ?string $phoneNumber = null;
    #[Assert\Length(max: 50)]
    private ?string $alternativeNumber = null;
    private ?string $extraInformation = null;
    private bool $isVerified = false;

    #[Ignore]
    public function isEmpty(): bool
    {
        return empty($this->getPhoneNumber());
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

    public function getExtraInformation(): ?string
    {
        return $this->extraInformation;
    }

    public function setExtraInformation(?string $extraInformation): self
    {
        $this->extraInformation = $extraInformation;

        return $this;
    }

    /**
     * Value to define if the main phoneNumber has been through verification.
     */
    public function getIsVerified(): bool
    {
        return $this->isVerified;
    }

    /**
     * Define if the main phoneNumber has been through verification.
     */
    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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
