<?php

namespace App\Services\Pagination;

use App\EmploymentDispute\Submission\Search\SearchFiltersInterface;
use App\Repository\PaginationRepositoryInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class Pagination
{
    public function __construct(private PaginatorInterface $paginator)
    {
    }

    public function createOptionsFromRequest(Request $request): PaginationOptions
    {
        $options = new PaginationOptions();
        if ($request->query->has($options->getQueryKey())) {
            $query = $request->query->get($options->getQueryKey());
            $options->setQuery($query);
        }

        $options->setCurrentPage($request->query->getInt('page', 1));

        return $options;
    }

    /**
     * @return PaginationInterface<mixed>
     */
    public function generatePagination(PaginationRepositoryInterface $repository, PaginationOptions $options, SearchFiltersInterface $searchFilters = null): PaginationInterface
    {
        $queryBuilder = $repository->getQueryBuilder($options->getQuery());

        if (!empty($searchFilters)) {
            $queryBuilder = $searchFilters->applyFilters($queryBuilder);
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder,
            $options->getCurrentPage(),
            $options->getPerPage()
        );

        return $pagination;
    }
}
