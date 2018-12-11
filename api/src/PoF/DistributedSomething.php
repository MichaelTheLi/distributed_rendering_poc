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
        $this->client->send('test');

        $this->logger->debug('onMessage');
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
