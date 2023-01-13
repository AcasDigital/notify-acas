<?php

namespace App\Services;

use App\EmploymentDispute\Tasks\TaskInterface;
use App\Entity\QuestionMetric;
use App\Repository\QuestionMetricRepository;
use Doctrine\ORM\EntityManagerInterface;

class QuestionMetricLogger
{
    public function __construct(private QuestionMetricRepository $questionMetricRepository, private EntityManagerInterface $em)
    {
    }

    public function logWizardMetric(string $id, $formData)
    {
        $questionMetric = $this->getOrCreateQuestion($id);
        if ($this->hasData($formData)) {
            $questionMetric->incrementFilled();
        } else {
            $questionMetric->incrementNotFilled();
        }

        $this->questionMetricRepository->add($questionMetric);
    }

    public function logTaskListMetrics($taskList)
    {
        $status = $taskList->getAllTaskStatus();
        foreach ($status as $section => $questions) {
            foreach ($questions as $id => $status) {
                $questionMetric = $this->getOrCreateQuestion($id);

                if (TaskInterface::STATUS_OPTIONAL === $status) {
                    $questionMetric->incrementNotFilled();
                } elseif (TaskInterface::STATUS_COMPLETE === $status) {
                    $questionMetric->incrementFilled();
                }

                $this->questionMetricRepository->add($questionMetric, false);
            }
        }

        $this->em->flush();
    }

    private function getOrCreateQuestion($id): QuestionMetric
    {
        $questionMetric = $this->questionMetricRepository->findOneByQuestion($id);
        if (!$questionMetric) {
            $questionMetric = new QuestionMetric();
            $questionMetric->setQuestion($id);
        }

        return $questionMetric;
    }

    private function hasData($form): bool
    {
        if (count($form) <= 1) {
            return false;
        }

        foreach ($form as $key => $value) {
            if ('_token' == $key) {
                continue;
            }

            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }
}
