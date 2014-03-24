<?php

namespace Spiffy\Dispatch\Exception;

class MissingDispatchableException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'The dispatchable with name "%s" does not exist',
            $name
        ));
    }
}
