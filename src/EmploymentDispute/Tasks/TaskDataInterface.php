<?php

namespace App\EmploymentDispute\Tasks;

use Symfony\Component\Serializer\Annotation\Ignore;

interface TaskDataInterface
{
    #[Ignore]
    public function isEmpty(): bool;
}
