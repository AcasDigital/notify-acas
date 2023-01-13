<?php

namespace App\Translation;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslatorService
{
    public function __construct(private TranslatorInterface $translator, private LoggerInterface $logger)
    {
    }

    /**
     * Retrieve admin editable text.
     *
     * @param array<string> $tokens
     */
    public function getText(string $translationKey, array $tokens = []): string
    {
        $key = strtolower($translationKey);
        $translation = $this->translator->trans($key, $tokens, 'messages', 'en');

        if ($translation === $key) {
            $this->logger->error("[TEXTREPLACEMENT] [missing-text] No replacement found for key: '$key'.");
        }

        return sprintf('<span data-key="%s">%s</span>', $key, $translation);
    }
}
