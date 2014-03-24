<?php

namespace Spiffy\Dispatch\Exception;

class DispatchableExistsException extends \InvalidArgumentException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'The dispatchable with name "%s" already exists',
            $name
        ));
    }
}
