<?php

namespace Sync;

use Illuminate\Database\Capsule\Manager as Capsule;

class DBConnection
{
    /** @var string Файл хранения данных аккаунта. */
    private const CONFIG_DB = './config/dbConfig.php';
    function connect()
    {
        $capsule = new Capsule();
        $obj = include self::CONFIG_DB;
        $capsule->addConnection($obj['database']);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}