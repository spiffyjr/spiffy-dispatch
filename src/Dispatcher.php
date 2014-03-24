<?php

namespace Spiffy\Dispatch;

class Dispatcher
{

    protected $instances;

    public function add($name, $object)
    {
        $this->instances[$name] = $object;
    }

    public function dispatch($name, array $params = [])
    {
        $object = $this->instances[$name];

        return $this->dispatchObject($object, $params);
    }

    protected function isDispatchable($object)
    {
        return $object instanceof \Closure || $object instanceof Dispatchable;
    }

    protected function dispatchObject($object, array $params = [])
    {
        if ($object instanceof \Closure) {
            echo 'was closure' . PHP_EOL;
            $result = $this->invokeClosure($object, $params);
        } else if ($object instanceof Dispatchable) {
            echo 'was dispatchable' . PHP_EOL;
            $result =$this->invokeDispatchable($object, $params);
        } else if (is_callable($object)) {
            echo 'handle callable';
            exit;
            return $object;
        } else {
            return $object;
        }

        return $this->dispatchObject($result, $params);
    }

    protected function invokeClosure(\Closure $closure, array $params = [])
    {
        $function = new \ReflectionFunction($closure);
        $args = [];

        foreach ($function->getParameters() as $funcParam) {
            if (isset($params[$funcParam->getName()])) {
                $args[] = $params[$funcParam->getName()];
            } else if (!$funcParam->isOptional()) {
                throw new Exception\MissingRequiredArgumentException($funcParam->getName());
            }
        }

        return $function->invokeArgs($args);
    }

    protected function invokeDispatchable(Dispatchable $dispatchable, array $params = [])
    {
        return $dispatchable->dispatch($params);
    }
}