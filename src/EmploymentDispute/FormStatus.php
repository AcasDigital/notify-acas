<?php

namespace App\EmploymentDispute;

class FormStatus
{
    public const NO_VERIFICATION = 'no-verification'; // wizard status
    public const PENDING = 'pending'; // wizard status
    public const VERIFIED = 'verified'; // wizard status
    public const DRAFT_IN_PROGRESS = 'draft-in-progress'; // form status - verified/no-verification will turn into in-progress when the form is created
    public const DRAFT_COMPLETED = 'draft-completed'; // form status
    public const SENT = 'sent'; // form status
}
