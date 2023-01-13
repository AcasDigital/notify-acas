<?php

namespace App\Command;

use App\Entity\EmploymentDispute;
use App\Repository\EmploymentDisputeRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'deploy:insolvency_update',
    description: 'Add a short description for your command',
)]
class DeployInsolvencyUpdate extends Command
{
    public function __construct(private EmploymentDisputeRepository $employmentDisputeRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach ($this->employmentDisputeRepository->findByStatus(EmploymentDispute::STATUS_DRAFT) as $employmentDispute) {
            $data = $employmentDispute->getData();
            foreach ($data['sectionData'] as $key => $value) {
                if (str_starts_with($key, 'respondent-')) {
                    $insolvent = $data['sectionData'][$key]['org_name']['orgOutOfBusiness'] ?? null;
                    if (is_null($insolvent)) {
                        continue;
                    }

                    if ($insolvent) {
                        $data['sectionData'][$key]['org_in_business']['data'] = 'no';
                        $io->writeln('Updated '.$employmentDispute->getId()." org_in_business set to 'no'");
                    } else {
                        $data['sectionData'][$key]['org_in_business']['data'] = 'yes';
                        $io->writeln('Updated '.$employmentDispute->getId()." org_in_business set to 'yes'");
                    }
                }
            }
            $employmentDispute->setData($data);
            $this->employmentDisputeRepository->add($employmentDispute);
        }

        return Command::SUCCESS;
    }
}
