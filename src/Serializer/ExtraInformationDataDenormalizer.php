<?php

namespace App\Serializer;

use App\EmploymentDispute\Tasks\ConfigFileTask;
use App\Form\Data\ExtraInformationData;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;

class ExtraInformationDataDenormalizer implements ContextAwareDenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = []): mixed
    {
        $object = new ExtraInformationData();
        $task = $context['task'];
        assert($task instanceof ConfigFileTask);
        if (!is_array($data)) {
            return $object;
        }

        $dataType = $task->getFormField()['data']['options']['field_type'] ?? 'string';
        $extraInfo = $data['extraInformation'] ?? null;
        $hasExtra = $data['hasExtra'] ?? null;

        if (!$hasExtra) {
            return $object;
        }
        $object->setHasExtra($hasExtra);

        if (!$extraInfo || 'no' === $hasExtra) {
            return $object;
        }

        if ('date' === $dataType && 'yes' === $hasExtra) {
            $date = new \DateTime($extraInfo);
            $object->setExtraInformation($date);
        } elseif ('yes' === $hasExtra) {
            $object->setExtraInformation($extraInfo);
        }

        return $object;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (!isset($context['task'])) {
            return false;
        }

        return ExtraInformationData::class === $type && $context['task'] instanceof ConfigFileTask;
    }
}
