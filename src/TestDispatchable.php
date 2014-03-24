<?php

namespace Spiffy\Dispatch;

class TestDispatchable implements Dispatchable
{
    public function dispatch(array $params)
    {
        echo 'invoked with:' . PHP_EOL;
        print_r($params);
    }
}