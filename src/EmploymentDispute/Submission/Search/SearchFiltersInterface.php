<?php

namespace App\EmploymentDispute\Submission\Search;

use Doctrine\ORM\QueryBuilder;

interface SearchFiltersInterface
{
    public function applyFilters(QueryBuilder $queryBuilder): QueryBuilder;
}
