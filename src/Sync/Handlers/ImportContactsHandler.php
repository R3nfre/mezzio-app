<?php

namespace Sync\Handlers;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\DBConnection;
use Sync\Models\Contact;
use Sync\Models\Account;
use Sync\Models\ContactNameEmail;
use Sync\Kommo\ApiService;
use Unisender\ApiWrapper\UnisenderApi;


class ImportContactsHandler implements RequestHandlerInterface
{
    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_FILE = './config/integration.php';

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
            $local['contactId'] = $contact->getId();
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
    private function importContacts(ServerRequestInterface $request): ResponseInterface
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $uni = new UnisenderApi(
            Account::where('user_name', $request->getQueryParams()['name'])->first()['unisender_api_key']
        );
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
        $dbConnect = new DBConnection();
        $dbConnect->connect();

        foreach ($contacts as $contact) {
            if ($contact['emails'] != null) {
                foreach ($contact['emails'] as $email) {
                    ContactNameEmail::create([
                        'email' => $email,
                        'contact_name' => $contact['name'],
                        'contact_id' => $contact['contactId']
                    ]);
                }
            }
        }

        $users = ContactNameEmail::all();
        foreach ($users as $user) {
            Contact::updateOrCreate([
                'fk_contact_name_email' => $user['pk_id']
            ], [
                'fk_account_id' => Account::where('user_name', $request->getQueryParams()['name'])->first()['pk_id']
            ]);
        }
        $result = $uni->importContacts([
            "field_names" => ["email", "Name"],
            "data" => $uniContacts
        ]);

        return new JsonResponse(
            json_decode($result)
        );
    }

    /**
     * @throws AmoCRMoAuthApiException
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->importContacts($request);
    }
}