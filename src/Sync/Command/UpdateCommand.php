<?php

namespace Sync\Command;

use Laminas\Cli\Command\AbstractParamAwareCommand;
use Laminas\Cli\Input\StringParam;
use Pheanstalk\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\Config\BeanstalkConfig;

class UpdateCommand extends AbstractParamAwareCommand
{
    protected static $defaultName = 'update_command';

    protected function configure()
    {
        $this->setName(self::$defaultName);
        $this->addParam(
            (new StringParam('update'))
                ->setShortcut('-t')
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $beanstalk = new BeanstalkConfig();
        $job = $beanstalk->getConnection()
            ->useTube('update')
            ->put(json_encode($input->getParam('update')));
        return 0;
    }

}