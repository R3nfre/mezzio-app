<?php

namespace Sync\Command;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends BaseWorkerCommand
{
    protected static $defaultName = 'worker';
    protected string $queue = 'times';

    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    public function process($data, OutputInterface $output)
    {
        $output->writeln('How time: ' . date('H:i') . ' (' . date('d.Y') . ')');
    }

}