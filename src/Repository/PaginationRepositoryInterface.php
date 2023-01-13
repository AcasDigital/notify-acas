<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;

interface PaginationRepositoryInterface
{
    public function getQueryBuilder(?string $query = null): QueryBuilder;
}
