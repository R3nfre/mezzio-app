<?php

declare(strict_types=1);

namespace Sync;

use Sync\Factories\WebhookHandlerFactory;
use Sync\Handlers\CreateUnisenderContactHandler;
use Sync\Handlers\WebhookHandler;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
            ],
            'factories'  => [
                \Sync\Handlers\SumHandler::class => \Sync\Factories\SumHandlerFactory::class,
                \Sync\Handlers\AuthorizeHandler::class => \Sync\Factories\AuthorizeHandlerFactory::class,
                \Sync\Handlers\ContactsHandler::class => \Sync\Factories\ContactsHandlerFactory::class,
                \Sync\Handlers\CreateUnisenderContactHandler::class => \Sync\Factories\CreateUnisenderContactHandlerFactory::class,
                \Sync\Handlers\WebhookHandler::class=>\Sync\Factories\WebhookHandlerFactory::class,
                \Sync\Handlers\GetUnisenderContactHandler::class=>\Sync\Factories\GetUnisenderContactHandlerFactory::class
            ],
        ];
    }

}
