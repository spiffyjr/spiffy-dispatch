<?php

namespace Spiffy\Dispatch\Exception;

class MissingRequiredArgumentException extends \RuntimeException
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf(
            'Closure failed to invoke: missing required parameter "%s"',
            $name
        ));
    }
}
