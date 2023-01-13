<?php

namespace App\Translation;

use App\Entity\Admin\Translation;
use App\Repository\Admin\TranslationRepository;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

class DbLoader implements LoaderInterface
{
    public function __construct(private TranslationRepository $repository)
    {
    }

    /**
     * Loads translations from the database.
     *
     * This is run on cache warmup and generates a cached file.
     */
    public function load(mixed $resource, string $locale, string $domain = 'messages'): MessageCatalogue
    {
        $messages = $this->repository->findAllWithKey();

        $values = array_map(static function (Translation $entity) {
            return $entity->getTranslation();
        }, $messages);

        $catalogue = new MessageCatalogue($locale, [
            $domain => $values,
        ]);

        return $catalogue;
    }
}
