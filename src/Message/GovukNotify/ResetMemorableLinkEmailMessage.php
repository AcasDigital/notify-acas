<?php

namespace App\Message\GovukNotify;

class ResetMemorableLinkEmailMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '96c4e7e1-7200-41f3-b0f5-998c87adf1bb';
    }
}
