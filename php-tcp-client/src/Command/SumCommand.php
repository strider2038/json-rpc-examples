<?php
/*
 * This file is part of php-tcp-client.
 *
 * (c) Igor Lazarev <strider2038@yandex.ru>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tivoka\Client\Connection\ConnectionInterface;
use Tivoka\Client\Request;

/**
 * @author Igor Lazarev <strider2038@yandex.ru>
 */
class SumCommand extends Command
{
    protected static $defaultName = 'math:sum';

    /** @var ConnectionInterface */
    private $client;

    public function __construct(ConnectionInterface $client)
    {
        $this->client = $client;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Run math.sum command by JSON-RPC protocol')
            ->addArgument(
                'numbers',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Numbers for calculating sum'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputNumbers = $input->getArgument('numbers');

        $numbers = [];

        foreach ($inputNumbers as $number) {
            $numbers[] = (int) $number;
        }

        $time = microtime(true);

        /** @var Request $request */
//        $request = $this->client->sendRequest('math.sum', $numbers);

        $client = stream_socket_client('tcp://localhost:8080', $errno, $errstr, 1, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);

        stream_set_timeout($client, 1);
        stream_set_blocking($client, 1);

        if (!$client) {
            throw new \RuntimeException('Cannot connect');
        }

        $request = json_encode([
            [
                "jsonrpc" => "2.0",
                "id" => 1,
                "method" => "math.sum",
                "params" => $numbers,
            ]
        ]);

        fwrite($client, $request);
        fwrite($client, "\n");
        fflush($client);

        $response = fgets($client);

        fclose($client);

        $elapsed = microtime(true) - $time;

        $output->writeln(sprintf('Response received, elapsed time is %.3f ms.', $elapsed * 1000));

        $output->writeln($response);

//        if ($request->isError()) {
//            $output->writeln(sprintf('Error: %s.', $request->errorMessage));
//        } else {
//            $output->writeln(sprintf('Result = %d.', $request->result));
//        }

    }
}
