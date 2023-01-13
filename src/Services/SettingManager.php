<?php

namespace App\Services;

use App\Entity\Setting;
use App\Repository\SettingRepository;
use Doctrine\ORM\EntityManagerInterface;

class SettingManager
{
    public const SUBMISSION_PAUSED = 'submission_paused';
    public const SUBMISSION_CLEANUP = 'submission_cleanup';

    public function __construct(private EntityManagerInterface $em, private SettingRepository $repository)
    {
    }

    public function set(string $key, ?string $value): ?string
    {
        $var = $this->repository->findOneByName($key);

        if (!$var) {
            $var = new Setting();
            $var->setName($key);
            $var->setValue($value);
            $this->em->persist($var);
        } else {
            $var->setValue($value);
        }

        $this->em->flush();

        return $value;
    }

    public function getBool(string $key, bool $default): bool
    {
        if (null != $this->get($key)) {
            return boolval($this->get($key));
        } else {
            return $default;
        }
    }

    public function getInt(string $key, int $default): int
    {
        if (null != $this->get($key)) {
            return intval($this->get($key));
        } else {
            return $default;
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->repository->findOneByName($key);

        if ($value) {
            return $value->getValue();
        }

        return $default;
    }
}
