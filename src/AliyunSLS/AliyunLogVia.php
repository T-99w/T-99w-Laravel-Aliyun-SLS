<?php

namespace App\Tools\AliyunSLS;
use Monolog\Logger;

class AliyunLogVia
{
    /**
     * @param array $config
     * @return Logger
     */
    public function __invoke(array $config)
    {
        $channel = $config['name'] ?? config('app.name');
        $monolog = new Logger($channel);
        $monolog->pushHandler(new AliyunHandler());
        return $monolog;
    }
}
