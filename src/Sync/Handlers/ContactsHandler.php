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

class ContactsHandler implements RequestHandlerInterface
{

    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_FILE = './config/integration.php';

    /**
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMApiException
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
        $apiClient = $apiService->getApiClient($request->getQueryParams()['name']);
        $answer = [];
        $contacts = $apiClient
            ->contacts()
            ->get();
        foreach ($contacts as $contact) {
            $customFields = $contact->getCustomFieldsValues();
            $local['name'] = $contact->toArray()['name'];
            if ($customFields != null) {
                foreach ($customFields->getBy('fieldCode', 'EMAIL')->getValues()->toArray() as $email) {
                    $local['emails'][] = $email['value'];
                }
                unset($email);
            } else {
                $local['emails'] = null;
            }
            $answer[] = $local;
            unset($local);
        }
        unset($contact);

        return new JsonResponse($answer);
    }
}
