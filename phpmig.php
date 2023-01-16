<?php

use Phpmig\Adapter;
use Pimple\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

$container = new Container();

$container['config'] = [
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'app_db',
    'username'  => 'admin',
    'password'  => '111111',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
];

$container['db'] = function ($c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

$container['phpmig.adapter'] = function($c) {
    return new Adapter\Illuminate\Database($c['db'], 'migrations');
};
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;