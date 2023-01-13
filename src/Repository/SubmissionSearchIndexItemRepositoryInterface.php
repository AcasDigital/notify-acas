<?php

namespace App\Repository;

interface SubmissionSearchIndexItemRepositoryInterface
{
    /**
     * @return mixed[]
     */
    public function getAllByTaskDetails(string $disputeFormId, string $type, string $section): array;
}
