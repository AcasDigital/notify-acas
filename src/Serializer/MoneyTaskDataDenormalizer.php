<?php

namespace App\Serializer;

use App\EmploymentDispute\Tasks\ConfigFileTask;
use App\EmploymentDispute\Tasks\Data\MoneyTaskData;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class MoneyTaskDataDenormalizer implements ContextAwareDenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        $object = new MoneyTaskData();
        if (!is_array($data)) {
            return $object;
        }

        $data = $data['data'] ?? null;
        if (is_float($data)) {
            $value = $data;
        } elseif (is_int($data)) {
            $value = floatval($data);
        } else {
            $value = null;
        }
        $object->setData($value);

        return $object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!isset($context['task'])) {
            return false;
        }

        return MoneyTaskData::class === $type && $context['task'] instanceof ConfigFileTask;
    }
}
