<?php

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

class CreateUnisenderContactHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $uni = new UnisenderApi('6fs8brcr9ay16cf8xngo8k3pw4khnhbjr48kq99e');

        $result = $uni->importContacts([
            "field_names" => ["email", "Name"],
            "data" => [[$request->getQueryParams()['email'], $request->getQueryParams()['name']]]
        ]);

        return new JsonResponse(
            json_decode($result)
        );
    }
}