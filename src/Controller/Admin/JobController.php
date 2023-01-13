<?php

namespace App\Controller\Admin;

use App\Job\JobInterface;
use App\Message\RunJobMessage;
use App\Repository\JobHistoryRepository;
use App\Services\Pagination\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/idr-bst/admin/tasks')]
class JobController extends AbstractController
{
    /**
     * @var iterable<JobInterface>
     */
    private $jobs;

    /**
     * @param iterable<JobInterface> $jobs
     */
    public function __construct(
        #[TaggedIterator('app.job', 'getId')] iterable $jobs,
        private JobHistoryRepository $repository,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        ) {
        $this->jobs = $jobs;
    }

    #[Route('/jobs', name: 'admin_jobs', methods: ['GET'])]
    public function adminServices(LockFactory $lock): Response
    {
        $status = [];
        foreach ($this->jobs as $key => $job) {
            $keyLock = $lock->createLock(strval($key));
            $status[$key] = false;
            if ($this->checkIfJobQueued(strval($key))) {
                $status[$key] = 'queued';
            } elseif (!$keyLock->acquire()) {
                $status[$key] = 'running';
            } else {
                $keyLock->release();
            }
        }

        return $this->render('admin/admin-jobs.html.twig', [
            'jobs' => $this->jobs,
            'status' => $status,
        ]);
    }

    #[Route('/jobs/history', name: 'admin_jobs_history', methods: ['GET'])]
    public function adminJobsHistory(Request $request, Pagination $searchPagination): Response
    {
        $options = $searchPagination->createOptionsFromRequest($request);
        $pagination = $searchPagination->generatePagination($this->repository, $options);

        if (!$pagination->count()) {
            throw new BadRequestException('No history found');
        }

        return $this->render('admin/admin-jobs-history.html.twig', [
            'type' => $request->get('q'),
            'pagination' => $pagination,
            'options' => $options,
        ]);
    }

    #[Route('/jobs', name: 'admin_jobs_run', methods: ['POST'])]
    public function adminJobRun(Request $request, MessageBusInterface $bus): Response
    {
        $type = (string) $request->request->get('type');
        $this->logger->info("[JOBRUN] Manually sending $type to queue");
        $backgroundTask = new RunJobMessage($type);
        $bus->dispatch($backgroundTask);

        return $this->redirectToRoute('admin_jobs');
    }

    private function checkIfJobQueued(string $type): bool
    {
        $conn = $this->em->getConnection();

        $sql = '
                SELECT * FROM messenger_messages m
                WHERE m.body = :message AND queue_name = :queue AND delivered_at IS null
                ';

        $message = ['id' => $type];
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery(['message' => json_encode($message), 'queue' => 'background']);

        return count($result->fetchAllAssociative()) >= 1;
    }
}
