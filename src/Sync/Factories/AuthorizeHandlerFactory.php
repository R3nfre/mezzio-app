<?php

declare(strict_types=1);

namespace Sync\Factories;

use Sync\Handlers\WebhookHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Handlers\AuthorizeHandler;


class AuthorizeHandlerFactory
{
    public function __invoke(ContainerInterface $container): AuthorizeHandler
    {
        return new AuthorizeHandler();
    }
}
