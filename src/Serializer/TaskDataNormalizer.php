<?php

namespace App\Serializer;

use App\EmploymentDispute\Tasks\TaskDataInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class TaskDataNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($topic, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($topic, $format, $context);

        if (is_array($data) && isset($data['address'])) {
            $data = [
                $data['address']['addressFirstLine'] ?? null,
                $data['address']['addressSecondLine'] ?? null,
                $data['address']['town'] ?? null,
                $data['address']['postcode'] ?? null,
            ];
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof TaskDataInterface && ($context['review'] ?? false) === true;
    }
}
