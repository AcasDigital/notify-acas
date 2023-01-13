<?php

namespace App\Message\GovukNotify;

class TwoFactorAuthCodeEmailMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '222993d4-e4aa-43fd-8b04-4944cc2412fb';
    }
}
