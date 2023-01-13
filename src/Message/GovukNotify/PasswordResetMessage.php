<?php

namespace App\Message\GovukNotify;

class PasswordResetMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '';
    }
}
