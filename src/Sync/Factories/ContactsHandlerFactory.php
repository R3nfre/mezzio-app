<?php

declare(strict_types=1);

namespace Sync\Factories;

use Sync\Handlers\ContactsHandler;
use Sync\Handlers\SumHandler;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Handlers\AuthorizeHandler;


class ContactsHandlerFactory
{

    public function __invoke(ContainerInterface $container): ContactsHandler
    {
        return new ContactsHandler();
    }
}
