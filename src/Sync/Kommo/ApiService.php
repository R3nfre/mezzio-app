<?php

namespace Sync\Kommo;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Sync\Models\Account;
use Sync\DBConnection;
use Illuminate\Database\Capsule\Manager as Capsule;

class ApiService
{
    /** @var string Базовый домен авторизации. */
    private const TARGET_DOMAIN = 'kommo.com';

    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_FILE = './config/integration.php';

    /** @var AmoCRMApiClient AmoCRM клиент. */
    private AmoCRMApiClient $apiClient;

    public function __construct(string $clientId, string $clientSecret, string $redirectUri)
    {
        $this->apiClient = new AmoCRMApiClient($clientId, $clientSecret, $redirectUri);
    }

    /**
     * Авторизация.
     *
     * @return string
     */
    public function auth(): string
    {
        session_start();

        if (isset($_GET['name'])) {
            $_SESSION['name'] = $_GET['name'];
            $dbConnect = new DBConnection();
            $dbConnect->connect();
            $account = new Account();
            if($account->query()
                ->where('user_name', '=', $_GET['name'])
                ->get()[0] != null){
                return $_SESSION['name'];
            }
        }

        if (isset($_GET['referer'])) {
            $this
                ->apiClient
                ->setAccountBaseDomain($_GET['referer'])
                ->getOAuthClient()
                ->setBaseDomain($_GET['referer']);
        }

        try {
            if (!isset($_GET['code'])) {
                $state = bin2hex(random_bytes(16));
                $_SESSION['oauth2state'] = $state;
                if (isset($_GET['button'])) {
                    echo $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getOAuthButton([
                            'title' => 'Установить интеграцию',
                            'compact' => true,
                            'class_name' => 'className',
                            'color' => 'default',
                            'error_callback' => 'handleOauthError',
                            'state' => $state,
                        ]);
                } else {
                    $authorizationUrl = $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getAuthorizeUrl([
                            'state' => $state,
                            'mode' => 'post_message',
                            'name' => $_SESSION['name']
                        ]);
                    header('Location: ' . $authorizationUrl);
                }
                die;
            } elseif (
                empty($_GET['state']) ||
                empty($_SESSION['oauth2state']) ||
                ($_GET['state'] !== $_SESSION['oauth2state'])
            ) {
                unset($_SESSION['oauth2state']);
                exit('Invalid state');
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        try {
            $accessToken = $this
                ->apiClient
                ->getOAuthClient()
                ->setBaseDomain($_GET['referer'])
                ->getAccessTokenByCode($_GET['code']);

            if (!$accessToken->hasExpired()) {
                $this->saveToken([
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                    'base_domain' => $this->apiClient->getAccountBaseDomain(),
                ]);
                $this->setWebhook($_SESSION['name']);
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $_SESSION['name'];
    }

    /**
     * Сохранение токена авторизации по имени аккаунта.
     *
     * @param array $token
     * @return void
     */
    private function saveToken(array $token): void
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $account = new Account();
        $account->query()
            ->updateOrCreate(
                [
                    'user_name' => $_SESSION['name']
                ],
                [
                    'user_name' => $_SESSION['name'],
                    'account_data' => json_encode($token)
                ]
            )
            ->save();
    }

    /**
     * Получение токена из файла по имени.
     *
     * @param string $accountName
     * @return AccessToken
     */
    public function readToken(string $accountName): AccessToken
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $account = new Account();
        $answer = $account->query()
            ->where('user_name', '=', $accountName)
            ->get('account_data');
        return new AccessToken(
            json_decode($answer[0]['account_data'], true)
        );
    }

    public function readBaseDomain(string $accountName): string
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        $account = new Account();
        $answer = $account->query()
            ->where('user_name', '=', $accountName)
            ->get('account_data');
        return json_decode($answer[0]['account_data'], true)['base_domain'];
    }

    public function getApiClient(string $name): AmoCRMApiClient
    {
        $this->apiClient
            ->setAccessToken($this->readToken($name))
            ->setAccountBaseDomain($this->readBaseDomain($name));
        return $this->apiClient;
    }

    /**
     * Сохранение токена авторизации по имени аккаунта.
     *
     * @param array $token
     * @return void
     * @throws AmoCRMMissedTokenException
     */
    private function setWebhook(string $name): void
    {
        $this->apiClient
            ->setAccessToken($this->readToken($name))
            ->setAccountBaseDomain($this->readBaseDomain($name));
        $webHookModel = (new \AmoCRM\Models\WebhookModel())
            ->setSettings([
                'add_contact',
                'update_contact',
                'delete_contact'
            ])
            ->setDestination('https://1249-173-233-147-68.eu.ngrok.io/webhook?name=' . $name);
        $this->apiClient
            ->webhooks()
            ->subscribe($webHookModel)
            ->toArray();
    }
}

