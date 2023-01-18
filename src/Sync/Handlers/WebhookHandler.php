<?php

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;
use function PHPUnit\Framework\isFalse;
use Sync\DBConnection;
use Sync\Models\Contact;
use Sync\Models\Account;
use Sync\Models\ContactNameEmail;


class WebhookHandler implements RequestHandlerInterface
{
    private function updateContactName(string $contactName, string $userName, int $idContact, array $emails)
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $uni = new UnisenderApi(Account::where('user_name', $userName)->first()['unisender_api_key']);
        $uniContacts = [];
        foreach ($emails as $email) {
            ContactNameEmail::updateOrCreate([
                'contact_id' => $idContact,
                'email' => $email
            ], [
                'contact_name' => $contactName
            ]);
            $localContact[] = $email;
            $localContact[] = $contactName;
            $uniContacts[] = $localContact;
            unset($localContact);
        }
        $this->addToUnisender($uni, $uniContacts);
    }

    private function addContact(string $contactName, string $userName, int $idContact, array $emails)
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $uni = new UnisenderApi(Account::where('user_name', $userName)->first()['unisender_api_key']);
        $uniContacts = [];
        foreach ($emails as $email) {
            ContactNameEmail::updateOrCreate([
                'contact_id' => $idContact,
                'email' => $email,
            ], [
                'contact_name' => $contactName,
                'contact_id' => $idContact
            ]);
            $localContact[] = $email;
            $localContact[] = $contactName;
            $uniContacts[] = $localContact;
            unset($localContact);
        }

        $users = ContactNameEmail::all();
        foreach ($users as $user) {
            Contact::updateOrCreate([
                'fk_contact_name_email' => $user['pk_id']
            ], [
                'fk_account_id' => Account::where('user_name', $userName)->first()['pk_id']
            ]);
        }
        $this->addToUnisender($uni, $uniContacts);
    }

    private function updateContactEmail(string $contactName, string $userName, int $idContact, array $newEmails)
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $uni = new UnisenderApi(Account::where('user_name', $userName)->first()['unisender_api_key']);
        $dbEmails = [];
        foreach (ContactNameEmail::where('contact_id', $idContact)->get() as $dbEmail) {
            $dbEmails[] = $dbEmail['email'];
        }
        $emailsToAdd = array_diff($newEmails, $dbEmails);
        $emailsToDelete = array_diff($dbEmails, $newEmails);
        if ($emailsToAdd != []) {
            $uniContacts = [];
            foreach ($emailsToAdd as $emailAdd) {
                ContactNameEmail::create([
                    'email' => $emailAdd,
                    'contact_name' => $contactName,
                    'contact_id' => $idContact
                ]);
                $localContact[] = $emailAdd;
                $localContact[] = $contactName;
                $uniContacts[] = $localContact;
                unset($localContact);
            }
            $users = ContactNameEmail::all();
            foreach ($users as $user) {
                Contact::updateOrCreate([
                    'fk_contact_name_email' => $user['pk_id']
                ], [
                    'fk_account_id' => Account::where('user_name', $userName)->first()['pk_id']
                ]);
            }
            $this->addToUnisender($uni, $uniContacts);
        }
        if ($emailsToDelete != []) {
            $uniContacts = [];
            foreach ($emailsToDelete as $emailDelete) {
                ContactNameEmail::where('email', $emailDelete)->delete();
                $localContact[] = 1;
                $localContact[] = $emailDelete;
                $localContact[] = $contactName;
                $uniContacts[] = $localContact;
                unset($localContact);
            }
            $answer = $uni->importContacts([
                "field_names" => ["delete", "email", "Name"],
                "data" => $uniContacts
            ]);
        }
    }

    private function addToUnisender($uni, $contacts)
    {
        $answer = $uni->importContacts([
            "field_names" => ["email", "Name"],
            "data" => $contacts
        ]);
        $logs = json_decode($answer, true)['result']['log'];
        $result[] = file_get_contents('./usilog.txt');
        foreach ($logs as $log) {
            $result[] = $log['message'] . PHP_EOL;
        }
        file_put_contents('./usilog.txt', $result);
    }

    public function synchronize(ServerRequestInterface $request)
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        if ($request->getParsedBody()['contacts']['add']) {
            $newData = $request->getParsedBody()['contacts']['add'];
        } else if ($request->getParsedBody()['contacts']['update']) {
            $newData = $request->getParsedBody()['contacts']['update'];
        }
        $emails = [];
        $contactId = $newData[0]['id'];
        $contactName = $newData[0]['name'];

        if ($newData[0]['custom_fields']) {
            foreach ($newData[0]['custom_fields'] as $custom_field) {
                if ($custom_field['code'] == 'EMAIL') {
                    foreach ($custom_field['values'] as $value) {
                        if ($value['enum'] == '358790') {
                            $emails[] = $value['value'];
                        }
                    }
                }
            }
        }

        if ($contactName != ContactNameEmail::where('contact_id', (int)$contactId)->first()['contact_name']) {
            $this->updateContactName($contactName, $request->getQueryParams()['name'], $contactId, $emails);
        }
        if ($request->getParsedBody()['contacts']['add'] && $emails != []) {
            $this->addContact($contactName, $request->getQueryParams()['name'], $contactId, $emails);
        }
        if ($request->getParsedBody()['contacts']['update']) {
            $this->updateContactEmail($contactName, $request->getQueryParams()['name'], $contactId, $emails);
        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->synchronize($request);
        return new JsonResponse('');
    }
}