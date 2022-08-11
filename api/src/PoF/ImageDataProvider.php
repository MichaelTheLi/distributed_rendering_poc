<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * Copyright (c) 2011-present Qualiteam software Ltd. All rights reserved.
 * See https://www.x-cart.com/license-agreement.html for license details.
 */

namespace PoF;

class ImageDataProvider {
    /**
     * @var integer
     */
    protected int $colorIndex;

    /**
     * @var integer
     */
    protected int $colorInc;

    public function __construct()
    {
        $this->init();
    }

    public function init(): void
    {
        $this->colorIndex = 0;
        $this->colorInc = 1;
    }
    public function data(int $width, int $height)
    {
        $log = [
            'width' => $width,
            'height' => $height,
        ];

        $data = [];
        $index = 0;
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
//                if (random_int(0, 255) % 15 === 0) {
                    $color = random_int(0, 255);
//                }
                $data[$index + 0] = $color;
                $data[$index + 1] = $color;
                $data[$index + 2] = $color;
                $data[$index + 3] = 255;
                $index += 4;
            }
        }

        $this->colorIndex += $this->colorInc * 10;

        $log['lastIndex'] = $index;
        $log['len'] = count($data);
        $log['len4'] = count($data) / 4;
        /** @noinspection ForgottenDebugOutputInspection */
        print_r(json_encode($log, JSON_PRETTY_PRINT));
        return $data;
    }
}
