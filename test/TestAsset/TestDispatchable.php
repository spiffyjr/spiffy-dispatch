<?php

namespace Spiffy\Dispatch\TestAsset;

use Spiffy\Dispatch\Dispatchable;

class TestDispatchable implements Dispatchable
{
    public function dispatch(array $params)
    {
        return $params;
    }
}
