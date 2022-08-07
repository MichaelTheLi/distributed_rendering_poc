<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace PoF;

use Monolog\Logger;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Ratchet\WebSocket\MessageComponentInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

class DistributedSomething implements MessageComponentInterface {
    const START_FULL_RENDER_MSG_CODE = 1;
    const FULL_RENDER_STARTED_MSG_CODE = 2;
    const FULL_RENDER_DONE_MSG_CODE = 3;
    /**
     * @var ConnectionInterface
     */
    protected $client;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var integer
     */
    protected $colorIndex;

    /**
     * @var integer
     */
    protected $colorInc;

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

        $this->colorIndex = 0;
        $this->colorInc = 1;
        $this->logger->debug("New connection! ({$conn->resourceId})");
    }

    public function onMessage(ConnectionInterface $from, MessageInterface $msg) {
        $messageData = unpack('C*', $msg);

        $messageData = array_values($messageData);

        if ($messageData[0] === static::START_FULL_RENDER_MSG_CODE) {
            $startedData = [
                self::FULL_RENDER_STARTED_MSG_CODE
            ];
            $this->client->send(new Frame(pack('C*', ...$startedData), true, Frame::OP_BINARY));
//            $this->client->send(json_encode([
//                'message' => 'started'
//            ]));

            $width = $messageData[1];
            $height = $messageData[2];

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

            $this->colorIndex += $this->colorInc * 10;
//            $this->logger->debug('Done in '. (microtime(true) - $started) . 's');
            $doneData = array_merge(
                [
                    self::FULL_RENDER_DONE_MSG_CODE,
                ],
                $data
            );
            $this->client->send(new Frame(pack('C*', ...$doneData), true, Frame::OP_BINARY));
//            $this->client->send(json_encode([
//                'message'   => 'done_full',
//                'imageData' => $data
//            ]));
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
