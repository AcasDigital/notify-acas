<?php

namespace App\Twig;

use App\Translation\TranslatorService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TextReplacementExtension extends AbstractExtension
{
    public function __construct(private TranslatorService $translator)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('editableText', [$this, 'editableText'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Retrieve admin editable text.
     *
     * @param array<string> $tokens
     */
    public function editableText(string $key, array $tokens = []): string
    {
        return $this->translator->getText($key, $tokens);
    }
}
