<?php

namespace Sync\Config;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class BeanstalkConfig
{
    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_BEANSTALK = './config/autoload/local.beanstalk.php';
    private ?Pheanstalk $connection;

    private array $config;

    public function __construct(ContainerInterface $container = null)
    {
        try {
            if ($container) {
                $this->config = $container->get('config')['beanstalk'];
            } else {
                $this->config = (include self::CONFIG_BEANSTALK)['beanstalk'];
            }
            $this->connection = Pheanstalk::create(
                $this->config['host'],
                $this->config['port'],
                $this->config['timeout']
            );
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            exit($e->getMessage());
        }
    }
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }

}