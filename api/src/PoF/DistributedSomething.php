<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace PoF;

use Monolog\Logger;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class DistributedSomething implements MessageComponentInterface {
    /**
     * @var ConnectionInterface
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * DistributedSomething constructor.
     *
     * @param $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }


    public function onOpen(ConnectionInterface $conn) {
        $this->client = $conn;

        $this->logger->debug("New connection! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, $msg) {
//        $this->logger->debug('onMessage');

        $messageData = json_decode($msg, true);
        if  ($messageData['message'] === 'render_image') {
            $this->client->send(json_encode([
                'message' => 'started'
            ]));

            $width = 100;
            $height = 100;
            for ($i = 0; $i < $width; $i++) {
                for ($j = 0; $j < $height; $j++) {
                    $data = [
                        'message' => 'pixel',
                        'index'   => $i * $width + $j,
                        'R'       => rand(0, 255),
                        'G'       => rand(0, 255),
                        'B'       => rand(0, 255),
                        'A'       => rand(0, 255),
                    ];
                    $this->client->send(json_encode($data));
                }
            }

            $this->client->send(json_encode([
                'message' => 'done'
            ]));
        } elseif ($messageData['message'] === 'render_full_image') {
            $this->client->send(json_encode([
                'message' => 'started'
            ]));

            $width = 200;
            $height = 100;

            $started = microtime(true);
//            $this->logger->debug('Started: '. $started);
            $data = [];
            $index = 0;
            $color = mt_rand(0, 255);
            for ($i = 0; $i < $width; $i++) {
                for ($j = 0; $j < $height; $j++) {
                    if (mt_rand(0, 255) % 15 === 0) {
                        $color = mt_rand(0, 255);
                    }
                    $data[$index + 0] = $color;
                    $data[$index + 1] = $color;
                    $data[$index + 2] = $color;
                    $data[$index + 3] = 255;
                    $index += 4;
                }
            }
//            $this->logger->debug('Done in '. (microtime(true) - $started) . 's');

            $this->client->send(json_encode([
                'message'   => 'done_full',
                'imageData' => $data,
                'startTime' => $messageData['startTime']
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->logger->debug("The connection is closed, ({$conn->resourceId})");
        $this->client = null;
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->logger->error($e);

        $conn->close();
    }
}
