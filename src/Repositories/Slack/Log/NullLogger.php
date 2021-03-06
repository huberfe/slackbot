<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 08/12/2017
 * Time: 22:30
 */

namespace Warlof\Seat\Slackbot\Repositories\Slack\Log;


class NullLogger implements LogInterface {

    public function log(string $message, array $context = [])
    {

    }

    public function debug(string $message, array $context = [])
    {

    }

    public function warning(string $message, array $context = [])
    {

    }

    public function error(string $message, array $context = [])
    {

    }

}
