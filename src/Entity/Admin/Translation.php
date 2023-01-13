<?php

namespace App\Entity\Admin;

use App\Repository\Admin\TranslationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslationRepository::class)]
class Translation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255, name: 'trans_key', unique: true)]
    private string $key;

    #[ORM\Column(type: 'text')]
    private string $translation;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): self
    {
        $this->key = strtolower($key);

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(?string $translation): self
    {
        if (null === $translation) {
            $translation = '';
        }

        $this->translation = $translation;

        return $this;
    }
}
