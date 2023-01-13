<?php

namespace App\Form\Data;

class ExtraInformationData
{
    private ?string $hasExtra = null;

    /**
     * @var mixed
     */
    private $extraInformation = null;

    public function isEmpty(): bool
    {
        return empty($this->getHasExtra());
    }

    public function getHasExtra(): ?string
    {
        return $this->hasExtra;
    }

    public function hasExtra(): bool
    {
        return 'yes' === $this->getHasExtra();
    }

    public function setHasExtra(?string $hasExtra): self
    {
        $this->hasExtra = $hasExtra;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExtraInformation()
    {
        return $this->extraInformation;
    }

    /**
     * @param mixed $extraInformation
     */
    public function setExtraInformation($extraInformation): self
    {
        $this->extraInformation = $extraInformation;

        return $this;
    }
}
