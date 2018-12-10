#!/usr/bin/env php
<?php

require_once __DIR__.'/vendor/autoload.php';

$connection = \Tivoka\Client::connect(['host' => 'localhost', 'port' => 8080]);

$request = $connection->sendRequest('math.sum', [1, 2, 3]);

var_dump($request);
