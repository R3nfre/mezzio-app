<?php

declare(strict_types=1);

namespace Sync\Handlers;

use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Filters\BaseEntityFilter;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Kommo\ApiService;
use AmoCRM\Client\AmoCRMApiClient;

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
//        $apiContacts = $apiService->getApiClient();
//        try {
//            $apiContacts->contacts();
//        }
//        catch(AmoCRMMissedTokenException $e) {
//            return new JsonResponse([
//                'error' => 'AmoCRMMissedTokenException'
//            ]);
//        }
        return new JsonResponse([
           'name' => $name
        ]);
    }
}
