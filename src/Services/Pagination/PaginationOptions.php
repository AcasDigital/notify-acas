<?php

namespace App\Services\Pagination;

class PaginationOptions
{
    public const DEFAULT_QUERY_KEY = 'q';
    public const DEFAULT_PER_PAGE = 50;

    private ?string $query = null;
    private string $queryKey = self::DEFAULT_QUERY_KEY;
    private int $perPage = self::DEFAULT_PER_PAGE;
    private int $currentPage = 1;

    public function __construct()
    {
    }

    public function getQueryKey(): string
    {
        return $this->queryKey;
    }

    public function setQueryKey(string $queryKey): self
    {
        $this->queryKey = $queryKey;

        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function setQuery(?string $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this;
    }
}
