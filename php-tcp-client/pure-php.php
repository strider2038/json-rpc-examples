#!/usr/bin/env php
<?php

$startUpTime = microtime(true);

$client = stream_socket_client('tcp://localhost:8080', $errno, $errstr, 1);

stream_set_timeout($client, 5);
stream_set_blocking($client, 1);

if (!$client) {
    throw new \RuntimeException('Cannot connect');
}

$request = json_encode([
    [
        "jsonrpc" => "2.0",
        "id" => 1,
        "method" => "math.sum",
        "params" => [1, 2, 3],
    ],
    [
        "jsonrpc" => "2.0",
        "id" => 2,
        "method" => "math.sum",
        "params" => [4, 5, 6],
    ],
]);

echo sprintf('Request: %s.', trim($request)) . PHP_EOL;

fwrite($client, $request);
fwrite($client, "\n");
fflush($client);

$response = fgets($client);

fclose($client);

$elapsedTime = microtime(true) - $startUpTime;

echo sprintf('Response received, elapsed time is %.3f ms.', $elapsedTime * 1000) . PHP_EOL;
echo sprintf('Response: %s.', trim($response)) . PHP_EOL;
