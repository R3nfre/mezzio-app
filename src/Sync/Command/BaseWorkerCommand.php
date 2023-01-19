<?php

namespace Sync\Command;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pimple\Psr11\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\Config\BeanstalkConfig;

abstract class BaseWorkerCommand extends Command
{

    protected Pheanstalk $connection;

    protected string $queue = 'default';

    final public function __construct()
    {
        parent::__construct();
        $beanstalk = new BeanstalkConfig();
        $this->connection = $beanstalk->getConnection();
    }

    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while ($job = $this->connection
            ->watchOnly($this->queue)
            ->ignore(PheanstalkInterface::DEFAULT_TUBE)
            ->reserve()
        ) {
            try {
                $this->process(json_decode(
                    $job->getData(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ), $output);
            } catch (\Throwable $exception) {
                $this->handleException($exception, $job);
            }
            $this->connection->delete($job);
        }
        return 0;
    }

    private function handleException(\Throwable $exception, Job $job): void
    {
        echo "Error Unhandled exception $exception" . PHP_EOL . $job->getData();
    }

    abstract public function process($data, OutputInterface $output);

}