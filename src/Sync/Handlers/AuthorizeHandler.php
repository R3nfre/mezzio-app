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
    private const CONFIG_FILE = './config/integration.php';


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $obj = include self::CONFIG_FILE;
        $apiService = new ApiService(
            $obj['clientId'],
            $obj['clientSecret'],
            $obj['redirectUri']
        );

        $name = $apiService->auth();

        return new JsonResponse([
            'name' => $name
        ]);
    }
}
