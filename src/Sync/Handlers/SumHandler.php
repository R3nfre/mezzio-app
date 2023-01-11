<?php

declare(strict_types=1);

namespace Sync\Handlers;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Kommo\ApiService;


class SumHandler implements RequestHandlerInterface
{
    private const CONFIG_FILE = './config/integration.php';

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMMissedTokenException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $obj = include self::CONFIG_FILE;

        $apiService = new ApiService(
            $obj['clientId'],
            $obj['clientSecret'],
            $obj['redirectUri']
        );
        $apiClient = $apiService->getApiClient('artyom2');
        $webHookModel = (new \AmoCRM\Models\WebhookModel())
            ->setSettings([
                'add_contact',
                'update_contact',
            ])
            ->setDestination('https://3795-212-46-197-210.eu.ngrok.io/webhook');

        $response = $apiClient
            ->webhooks()
            ->subscribe($webHookModel)
            ->toArray();

        return new JsonResponse([
            $response
        ]);

    }
}
