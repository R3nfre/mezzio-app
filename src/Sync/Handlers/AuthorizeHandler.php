<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Kommo\ApiService;


class AuthorizeHandler implements RequestHandlerInterface
{
    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_FILE = 'src/Sync/config.json';

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $obj = include './config/integration.php';
        $apiService = new ApiService(
            $obj['clientId'],
            $obj['clientSecret'],
            $obj['redirectUri']
        );
        $apiService->auth();

        return new JsonResponse("");
    }
}
