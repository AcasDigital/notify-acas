<?php

namespace App\Message;

final class SubmissionMessage
{
    public function __construct(private string $disputeFormId, private ?string $customPayload = null, private bool $forcedSubmission = false)
    {
    }

    public function getDisputeFormId(): string
    {
        return $this->disputeFormId;
    }

    public function getCustomPayload(): ?string
    {
        return $this->customPayload;
    }

    public function isForcedSubmission(): bool
    {
        return $this->forcedSubmission;
    }
}
