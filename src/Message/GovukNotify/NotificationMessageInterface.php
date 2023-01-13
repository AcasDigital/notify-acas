<?php

namespace App\Message\GovukNotify;

interface NotificationMessageInterface
{
    /**
     * Govuk Notify template id.
     */
    public function getTemplate(): string;

    /**
     * Govuk Notify contact. It can be either an email or a mobile number in the govuk Notify accepted formats.
     */
    public function getContactInfo(): string;

    /**
     * Govuk Notify template personalisation details.
     *
     * @return array<string, string>
     */
    public function getPersonalisation(): array;
}
