<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Mudproxy\MudServer;
use React\EventLoop\Factory;
use React\Stream\Stream;

$loop = Factory::create();
$server = new MudServer($loop);
$server->start();

$read = new Stream(fopen('php://stdin', 'r+'), $loop);

$read->on('data', function($data){
    echo $data;
});


$loop->run();