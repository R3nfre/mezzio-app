<?php

namespace Sync\Command;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use Exception;
use League\OAuth2\Client\Token\AccessToken;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sync\DBConnection;
use Sync\Kommo\ApiService;
use Sync\Models\Account;

class UpdateWorkerCommand extends BaseWorkerCommand
{
    private const CONFIG_FILE = './config/integration.php';
    protected static $defaultName = 'update-worker';
    protected string $queue = 'update';

    protected function configure()
    {
        $this->setName(self::$defaultName);
    }

    public function process($data, OutputInterface $output)
    {
        $obj = include self::CONFIG_FILE;

        try {
            $output->writeln(
                sprintf(
                    'Updated: %d',
                    (new ApiService(
                        $obj['clientId'], $obj['clientSecret'], $obj['redirectUri']
                    ))->refreshToken($data)
                )
            );
        } catch (AmoCRMoAuthApiException|Exception $e) {
            $output->writeln("<error>{$e->getMessage()}<error>");
        }

//        $apiService = new ApiService(
//            $obj['clientId'],
//            $obj['clientSecret'],
//            $obj['redirectUri']
//        );
//        $dbConnect = new DBConnection();
//        $dbConnect->connect();
//        $userModel = Account::orderBy('updated_at', 'DESC')->first();
//        $apiClient = $apiService->getApiClient($userModel['user_name']);
//        $test = new AccessToken(json_decode($userModel['account_data'], true));
//        try {
//            $newToken = $apiClient
//                ->getOAuthClient()
//                ->setBaseDomain(json_decode($userModel['account_data'], true)['base_domain'])
//                ->getAccessTokenByRefreshToken($test);
//        }
//        catch (\Throwable $e){
//            die($e->getMessage());
//        }
//        $tokenToSave = [
//            'access_token' => $newToken->getToken(),
//            'refresh_token' => $newToken->getRefreshToken(),
//            'expires' => $newToken->getExpires(),
//            'base_domain' => $apiClient->getAccountBaseDomain(),
//        ];
//        Account::query()->update(['account_data' => json_encode($tokenToSave)]);
    }

}