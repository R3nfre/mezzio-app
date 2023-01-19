<?php

namespace Sync\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TimeCommand extends Command
{
    protected static $defaultName = 'how-time';

    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('How time: ' . date('H:i') . ' (' . date('d.Y') . ')');
        return 0;
    }

}