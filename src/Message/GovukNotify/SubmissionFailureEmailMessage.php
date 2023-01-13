<?php

namespace App\Message\GovukNotify;

class SubmissionFailureEmailMessage extends NotificationBaseMessage implements NotificationMessageInterface
{
    public function getTemplate(): string
    {
        return '1852853f-0344-45a6-8db1-9b98bff3a499';
    }
}
