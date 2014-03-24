<?php

namespace Spiffy\Dispatch\Exception;

class InvalidDispatchableException extends \RuntimeException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'Failed to dispatch: the dispatchable identified by "%s" is invalid',
            $name
        ));
    }
}
