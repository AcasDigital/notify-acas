<?php

namespace App\Command;

use App\Job\JobInterface;
use App\Message\RunJobMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'job:run',
    description: 'Add a short description for your command',
)]
class JobRunCommand extends Command
{
    /**
     * @var iterable<JobInterface>
     */
    private $jobs;

    /**
     * @param iterable<JobInterface> $jobs
     */
    public function __construct(#[TaggedIterator('app.job', 'id')] iterable $jobs, private MessageBusInterface $bus, private LoggerInterface $logger)
    {
        parent::__construct();
        $this->jobs = $jobs;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->jobs as $key => $job) {
            if (!$job->shouldRun()) {
                $this->logger->info('[JOBRUN] '.strval($key).' not scheduled for running');
            } else {
                $this->logger->info('[JOBRUN] Sending '.strval($key).' to queue');
                $backgroundTask = new RunJobMessage(strval($key));
                $this->bus->dispatch($backgroundTask);
            }
        }

        return Command::SUCCESS;
    }
}
