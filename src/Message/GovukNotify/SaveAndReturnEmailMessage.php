<?php

namespace App\Message\GovukNotify;

class SaveAndReturnEmailMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '68ebfb8f-8256-4528-b193-2a633aad8a03';
    }
}
