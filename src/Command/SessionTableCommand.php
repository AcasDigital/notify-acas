<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'session:create_table',
    description: 'Create the session table in the database',
)]
class SessionTableCommand extends Command
{
    public function __construct(private \Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler $pdoSessionHandler)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->pdoSessionHandler->createTable();

        return Command::SUCCESS;
    }
}
