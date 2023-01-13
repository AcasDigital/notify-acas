<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('formatJson', [$this, 'formatJson']),
        ];
    }

    public function formatJson(string $value): string
    {
        $normalized = json_decode($value);

        $json = json_encode($normalized, JSON_PRETTY_PRINT);

        return false === $json ? 'Error encoding json' : $json;
    }
}
