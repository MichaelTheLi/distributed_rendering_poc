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
     * @var ImageDataProvider
     */
    protected $imageDataProvider;
    /**
     * DistributedSomething constructor.
     *
     * @param $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;

        $this->imageDataProvider = new ImageDataProvider();
    }


    public function onOpen(ConnectionInterface $conn) {
        $this->client = $conn;

        $this->imageDataProvider->init();
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

            $width = $messageData[1];
            $height = $messageData[2];


            $data = $this->imageDataProvider->data($width, $height);

            $doneData = array_merge(
                [
                    self::FULL_RENDER_DONE_MSG_CODE,
                ],
                $data
            );
            $this->client->send(new Frame(pack('C*', ...$doneData), true, Frame::OP_BINARY));
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
