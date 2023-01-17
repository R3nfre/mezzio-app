<?php

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sync\DBConnection;
use Sync\Models\Account;

class WidgetHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $dbConnect = new DBConnection();
        $dbConnect->connect();
        if ($request->getMethod() == 'GET') {
            return new JsonResponse('');
        }

        if (!Account::where('user_name', $request->getParsedBody()['uname'])->first()) {
            return new JsonResponse('Account with given name does not exist');
        }

        $updatedAccount = Account::updateOrCreate([
            'user_name' => $request->getParsedBody()['uname']
        ], [
            'unisender_api_key' => $request->getParsedBody()['token']
        ]);
        return new JsonResponse($updatedAccount->user_name);
    }
}