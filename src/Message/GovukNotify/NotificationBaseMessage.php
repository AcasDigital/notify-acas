<?php

namespace App\Message\GovukNotify;

abstract class NotificationBaseMessage implements NotificationMessageInterface
{
    /**
     * @param array<string, string> $personalisation
     */
    public function __construct(private string $contactInfo, private array $personalisation)
    {
    }

    public function getContactInfo(): string
    {
        return $this->contactInfo;
    }

    /**
     * @return array<string, string>
     */
    public function getPersonalisation(): array
    {
        return $this->personalisation;
    }
}
