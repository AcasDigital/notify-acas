<?php

namespace App\EmploymentDispute\Tasks\Data;

use App\Form\Data\FullNameData;

interface FullNameDataInterface
{
    public function getFullName(): ?FullNameData;
}
