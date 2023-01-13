<?php

namespace App\Entity;

use App\Repository\QuestionMetricRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuestionMetricRepository::class)]
class QuestionMetric
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $question;

    #[ORM\Column(type: 'integer')]
    private $notFilled = 0;

    #[ORM\Column(type: 'integer')]
    private $filled = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getNotFilled(): ?int
    {
        return $this->notFilled;
    }

    public function setNotFilled(int $notFilled): self
    {
        $this->notFilled = $notFilled;

        return $this;
    }

    public function getFilled(): ?int
    {
        return $this->filled;
    }

    public function setFilled(int $filled): self
    {
        $this->filled = $filled;

        return $this;
    }

    public function incrementFilled(): self
    {
        ++$this->filled;

        return $this;
    }

    public function incrementNotFilled(): self
    {
        ++$this->notFilled;

        return $this;
    }
}
