<?php

namespace App\Message\GovukNotify;

class VerificationSMSMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '2807a898-9709-40f1-af79-3d5a7a26c79c';
    }
}
