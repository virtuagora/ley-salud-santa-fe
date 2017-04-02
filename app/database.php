<?php

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'virtuagora_salud',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => '',
    // Habilitar strict mode si mySql esta instalado
    // https://github.com/laravel/framework/issues/3602
     'strict' => true
]);
$capsule->setEventDispatcher(new Illuminate\Events\Dispatcher());
$capsule->setAsGlobal();
$capsule->bootEloquent();
date_default_timezone_set('America/Argentina/Buenos_Aires');
