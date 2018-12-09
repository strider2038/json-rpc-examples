<?php
/*
 * This file is part of php-tcp-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\JsonRpc;

use Tivoka\Client;
use Tivoka\Client\Connection\ConnectionInterface;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class ClientFactory
{
    public function createClient(string $host, int $port): ConnectionInterface
    {
        return Client::connect(['host' => $host, 'port' => $port]);
    }
}
