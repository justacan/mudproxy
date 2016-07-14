<?php

namespace Mudproxy;

use React\Dns\Resolver\Factory;
use React\SocketClient\Connector;
use React\Stream\Stream;

class MudClient {
    private $loop, $tcpConnector, $dns, $conn, $stream;

    public function __construct(&$loop, $conn)
    {
        $this->loop = $loop;
        $this->conn = $conn;
        $dnsResolverFactory = new Factory();
        $this->dns = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);
        $this->tcpConnector = new Connector($this->loop, $this->dns);

    }

    public function send($message)
    {
//        echo "tring to send: $message\r\n";
        if (!is_null($this->stream) && $this->stream->isWritable()) {
            $this->stream->write($message);
        } else {
            $d = "Error\r\n";
            echo $d;
            $this->conn->write($d);
        }
    }

    public function check(){
        var_dump($this->stream->isReadable());
        var_dump($this->stream->isWritable());
    }

    public function connect($address, $port)
    {

        $this->conn->write("Starting...\r\n");
        $this->tcpConnector->create($address, $port)->then(
            function (Stream $stream) {
                $this->stream = $stream;
                $this->streamHandler($stream);
            });
    }

    private function streamHandler($stream)
    {
        $stream->on('data', function($data) use ($stream) {
            $this->conn->write($data);

            $logged = $this->format($data);
            echo $logged;

//            file_put_contents(__DIR__ . "/log.txt", $logged, FILE_APPEND);
        });
    }

    private function format($data)
    {
        // Remove ANSI Colors
        $data = preg_replace('/\x1b\[[0-9;]*m/', '', $data);
        // Remove Non 'keyboard' ASCII
        $data = preg_replace('/(?:[^\x20-\x7e\xa])/', '', $data);

        return $data;
    }
}