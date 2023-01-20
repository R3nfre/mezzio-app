<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pheanstalk\Pheanstalk;
use Sync\Config\BeanstalkConfig;


class ProducerHandler implements RequestHandlerInterface
{
    /** @var string . */
    private const CONFIG_BEANSTALK = './config/autoload/beanstalk.global.php';

    private ContainerInterface $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container=$container;
    }
    private function createProducer(): array
    {
        $beanstalk = new BeanstalkConfig($this->container);
        $job = $beanstalk->getConnection()
            ->useTube('times')
            ->put(json_encode('Ваше время'));
        return ['Job ID' => $job->getId()];
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse($this->createProducer());
    }
}
