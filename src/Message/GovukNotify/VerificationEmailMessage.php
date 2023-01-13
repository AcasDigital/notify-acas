<?php

namespace App\Message\GovukNotify;

class VerificationEmailMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '542ccaee-02b8-44cf-b21a-fbdb61e7936c';
    }
}
