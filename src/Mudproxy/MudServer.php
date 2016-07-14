<?php

namespace Mudproxy;

use React\Socket\Server;

class MudServer {
    private $loop;
    private $socket;
    private $conn;
    private $client;

    public function __construct(&$loop)
    {
        $this->loop = $loop;
        $this->socket = new Server($this->loop);
    }

    public function start()
    {
        $this->socket->on('connection', function ($conn) {
            $this->conn = $conn;
            $conn->on('data', function ($data) use ($conn) {
                $this->recvHandler($data);
            });
        });
        $this->socket->listen(4545, '0.0.0.0');
    }

    private function recvHandler($data)
    {
        $line = trim($data);
//        echo $line . "\n";

        if (preg_match('/^connect\s+(.*?)\s+(\d+)(?:\s+)?$/', $line, $matches)) {
            $this->client = new MudClient($this->loop, $this->conn);
            $this->client->connect($matches[1], $matches[2]);
        }

        if ($line == 'check') {
            $this->client->check();
        }

        if ($this->client) {
            $this->client->send($data);
        }

    }

    public function send($message)
    {
        $this->conn->write($message . "\r\n");
    }
}