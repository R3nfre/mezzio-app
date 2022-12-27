<?php

declare(strict_types=1);

namespace Sync\Factories;

use Sync\Handlers\ForwardingHandler;
use Sync\Handlers\SumHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Handlers\AuthorizeHandler;


class ForwardingHandlerFactory
{
    public function __invoke(ContainerInterface $container): ForwardingHandler
    {
        return new ForwardingHandler();
    }
}
