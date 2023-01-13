<?php

namespace App\Form\Data;

class ChoiceExtraData
{
    private ?string $hasExtra = null;

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
}
