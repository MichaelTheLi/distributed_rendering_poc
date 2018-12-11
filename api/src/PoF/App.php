<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace PoF;

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

class App
{
    public function run()
    {
        $logger = new \Monolog\Logger('main');

        $maxFiles = 10;

        $logger->pushHandler(
            new \Monolog\Handler\RotatingFileHandler(ROOT_DIR . '/logs/app.log', $maxFiles)
        );

        \Monolog\ErrorHandler::register($logger);

//        $server = IoServer::factory(
//            new DistributedSomething(),
//            8085
//        );

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new DistributedSomething($logger)
                )
            ),
            8080
        );

        $server->run();
    }
}
