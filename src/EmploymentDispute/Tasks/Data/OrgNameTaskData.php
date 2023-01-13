<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

class OrgNameTaskData implements TaskDataInterface
{
    #[Assert\NotBlank(message: "Enter the organisation's legal name")]
    #[Assert\Length(max: 160)]
    private ?string $orgName = null;
    private bool $stillWorkingForOrg = false;

    #[Ignore]
    public function isEmpty(): bool
    {
        return empty($this->getOrgName());
    }

    /**
     * Get the value of orgName.
     */
    public function getOrgName(): ?string
    {
        return $this->orgName;
    }

    /**
     * Set the value of orgName.
     */
    public function setOrgName(?string $orgName): self
    {
        $this->orgName = $orgName;

        return $this;
    }

    /**
     * Get the value of stillWorkingForOrg.
     */
    public function getStillWorkingForOrg(): bool
    {
        return $this->stillWorkingForOrg;
    }

    /**
     * Set the value of stillWorkingForOrg.
     */
    public function setStillWorkingForOrg(bool $stillWorkingForOrg): self
    {
        $this->stillWorkingForOrg = $stillWorkingForOrg;

        return $this;
    }
}
