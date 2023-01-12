<?php

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Unisender\ApiWrapper\UnisenderApi;

class GetUnisenderContactHandler implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $uni = new UnisenderApi('6fs8brcr9ay16cf8xngo8k3pw4khnhbjr48kq99e');

//        $result = $uni->importContacts([
//               "field_names" => ["email", "Name"],
//               "data" => [["egor@mail.ru", "egor"]]
//            ]);
//        $result = $uni->importContacts([
//            "field_names" => ["email", "Name"],
//            "data" => [["egor@mail.ru", "newEgor"]]
//        ]);
//        $result = $uni->importContacts([
//            "field_names" => ["email", "delete"],
//            "data" => [["egor@mail.ru", "1"]]
//        ]);
        $result = $uni->getContact([
                "email" => $request->getQueryParams()['email'],
                "include_fields" => "1"
            ]);
        return new JsonResponse(
            json_decode($result)
        );
    }
}