<?php

namespace Sync\Handlers;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\Kommo\ApiService;
use Unisender\ApiWrapper\UnisenderApi;


class ImportContactsHandler implements RequestHandlerInterface
{
    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_FILE = './config/integration.php';

    /** @var string Файл хранения данных аккаунта. */
    private const UNI_CONFIG_FILE = './config/uni.php';

    /**
     * Получение контактов AmoCRM.
     *
     * @param string $name
     * @return array
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     */
    public function getAmoCRMContacts(string $name): array
    {
        $obj = include self::CONFIG_FILE;
        $apiService = new ApiService(
            $obj['clientId'],
            $obj['clientSecret'],
            $obj['redirectUri']
        );
        $apiClient = $apiService->getApiClient($name);
        $contacts = $apiClient
            ->contacts()
            ->get();
        $amoContacts = [];
        foreach ($contacts as $contact) {
            $customFields = $contact->getCustomFieldsValues();
            $local['name'] = $contact->toArray()['name'];
            if ($customFields !== null) {
                foreach ($customFields->getBy('fieldCode', 'EMAIL')->getValues()->toArray() as $email) {
                    if ($email['enum_code'] == 'WORK') {
                        $local['emails'][] = $email['value'];
                    }
                }
                unset($email);
            } else {
                $local['emails'] = null;
            }
            $amoContacts[] = $local;
            unset($local);
        }
        unset($contact);
        return $amoContacts;
    }

    /**
     * @throws AmoCRMApiException
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMMissedTokenException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $obj = include self::UNI_CONFIG_FILE;
        $uni = new UnisenderApi($obj['apiKey']);
        $contacts = $this->getAmoCRMContacts($request->getQueryParams()['name']);
        $uniContacts = [];
        foreach ($contacts as $contact) {
            $localContact = [];
            if ($contact['emails'] != null) {
                foreach ($contact['emails'] as $email) {
                    $localContact[] = $email;
                    $localContact[] = $contact['name'];
                    $uniContacts[] = $localContact;
                    unset($localContact);
                }
            }
        }

        $result = $uni->importContacts([
            "field_names" => ["email", "Name"],
            "data" => $uniContacts
        ]);

        return new JsonResponse(
            json_decode($result)
        );
    }
}