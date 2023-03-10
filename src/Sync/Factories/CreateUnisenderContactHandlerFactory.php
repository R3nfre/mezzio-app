<?php

namespace Sync\Factories;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Handlers\CreateUnisenderContactHandler;
use Sync\Handlers\WebhookHandler;

class CreateUnisenderContactHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        return new CreateUnisenderContactHandler();
    }
}