<?php

declare(strict_types=1);

use Mezzio\Application;
use Mezzio\MiddlewareFactory;
use Psr\Container\ContainerInterface;

/**
 * FastRoute route configuration
 *
 * @see https://github.com/nikic/FastRoute
 *
 * Setup routes with a single request method:
 *
 * $app->get('/', App\Handler\HomePageHandler::class, 'home');
 * $app->post('/album', App\Handler\AlbumCreateHandler::class, 'album.create');
 * $app->put('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.put');
 * $app->patch('/album/{id:\d+}', App\Handler\AlbumUpdateHandler::class, 'album.patch');
 * $app->delete('/album/{id:\d+}', App\Handler\AlbumDeleteHandler::class, 'album.delete');
 *
 * Or with multiple request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class, ['GET', 'POST', ...], 'contact');
 *
 * Or handling all request methods:
 *
 * $app->route('/contact', App\Handler\ContactHandler::class)->setName('contact');
 *
 * or:
 *
 * $app->route(
 *     '/contact',
 *     App\Handler\ContactHandler::class,
 *     Mezzio\Router\Route::HTTP_METHOD_ANY,
 *     'contact'
 * );
 */

return static function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    $app->get('/sum', Sync\Handlers\SumHandler::class, 'sum');
    $app->get('/contacts', Sync\Handlers\ContactsHandler::class, 'contacts');
    $app->route('/authorize', Sync\Handlers\AuthorizeHandler::class)->setName('authorize');
    $app->get('/createUnisenderContact', Sync\Handlers\ImportContactsHandler::class, 'createUnisenderContact');
    $app->get('/getUnisenderContact', Sync\Handlers\GetUnisenderContactHandler::class, 'getUnisenderContact');
    $app->route('/webhook', Sync\Handlers\WebhookHandler::class)->setName('webhook');
    $app->get('/importContacts', Sync\Handlers\ImportContactsHandler::class, 'importContacts');
    $app->route('/widget', Sync\Handlers\WidgetHandler::class)->setName('widget');
    $app->route('/producer', Sync\Handlers\ProducerHandler::class)->setName('producer');
};
