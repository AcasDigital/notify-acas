<?php

namespace App\EmploymentDispute\Tasks;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Yaml\Yaml;

class TaskLocator
{
    /**
     * @var array<mixed>
     */
    private array $tasks = [];
    /**
     * @var array<string, string[]>
     */
    private array $rfd = [];

    public function __construct(private DenormalizerInterface $serializer)
    {
        $finder = new Finder();
        $files = $finder->in(__DIR__.'/config')->files()->name('*.yaml')->getIterator();
        $tasks = [];
        $rfd = [];
        foreach ($files as $file) {
            $file_config = Yaml::parse($file->getContents());
            assert(is_array($file_config));
            $tasks = array_merge($tasks, $file_config['tasks'] ?? []);
            $rfd = array_merge($rfd, $file_config['rfd'] ?? []);
        }

        $this->tasks = $tasks;
        $this->rfd = $rfd;
    }

    public function getTaskById(string $id): TaskInterface
    {
        foreach ($this->tasks as $key => $task) {
            if ($id === $key) {
                assert(is_array($task));

                return new ConfigFileTask($key, $task);
            }
        }

        throw new \InvalidArgumentException("Tasks not found with id: $id");
    }

    /**
     * @param array<string, string> $data
     */
    public function hydrateTaskData(string $id, array $data): TaskDataInterface
    {
        $task = $this->getTaskById($id);
        $dataClass = $task->getDataClass();

        return $this->serializer->denormalize($data, $dataClass);
    }

    /**
     * @param string[] $rfds
     *
     * @return TaskInterface[]
     */
    public function getTasksByRFD(array $rfds): array
    {
        $ids = [];
        foreach ($this->rfd as $key => $rfd_list) {
            if (in_array($key, $rfds)) {
                $ids = array_merge($ids, $rfd_list);
            }
        }

        $ids = array_unique($ids);

        $tasks = [];
        foreach ($ids as $id) {
            $tasks[] = $this->getTaskById($id);
        }

        usort($tasks, fn ($a, $b) => $a->getWeight() <=> $b->getWeight());

        return $tasks;
    }
}
