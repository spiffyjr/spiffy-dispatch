<?php

namespace Spiffy\Dispatch;

class Dispatcher
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @param string $name
     * @param mixed $dispatchable
     * @throws Exception\DispatchableExistsException
     */
    public function add($name, $dispatchable)
    {
        if (isset($this->instances[$name])) {
            throw new Exception\DispatchableExistsException($name);
        }

        $this->instances[$name] = $dispatchable;
    }

    /**
     * @param string $name
     * @param array $params
     * @return mixed
     * @throws Exception\MissingDispatchableException
     */
    public function dispatch($name, array $params = [])
    {
        if (!isset($this->instances[$name])) {
            throw new Exception\MissingDispatchableException($name);
        }

        $object = $this->instances[$name];

        return $this->dispatchObject($object, $params);
    }

    /**
     * Proxy to dispatch. Why you ask? Because $d->ispatch() is so damn cute, that's why.
     *
     * @see dispatch
     */
    public function ispatch($name, array $params = [])
    {
        return $this->dispatch($name, $params);
    }

    /**
     * @param $object
     * @param array $params
     * @return mixed
     */
    protected function dispatchObject($object, array $params = [])
    {
        if ($object instanceof \Closure) {
            $result = $this->dispatchClosure($object, $params);
        } else if ($object instanceof Dispatchable) {
            $result = $this->dispatchDispatchable($object, $params);
        } else if (is_object($object) && is_callable($object)) {
            $result = $this->dispatchInvokable($object, $params);
        } else if (is_callable($object)) {
            $result = $this->dispatchCallable($object, $params);
        } else {
            return $object;
        }

        return $this->dispatchObject($result, $params);
    }

    /**
     * @param $callable
     * @param array $params
     * @return mixed
     */
    protected function dispatchCallable($callable, array $params = [])
    {
        if (is_string($callable)) {
            $callable = explode('::', $callable);
        }

        $function = new \ReflectionMethod($callable[0], $callable[1]);
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return $function->invokeArgs(new $function->class, $args);
    }

    /**
     * @param $object
     * @param array $params
     * @return mixed
     */
    protected function dispatchInvokable($object, array $params = [])
    {
        $function = new \ReflectionMethod($object, '__invoke');
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return $function->invokeArgs($object, $args);
    }

    /**
     * @param callable $closure
     * @param array $params
     * @return mixed
     */
    protected function dispatchClosure(\Closure $closure, array $params = [])
    {
        $function = new \ReflectionFunction($closure);
        $args = $this->buildArgsFromReflectionFunction($function, $params);

        return $function->invokeArgs($args);
    }

    /**
     * @param Dispatchable $dispatchable
     * @param array $params
     * @return mixed
     */
    protected function dispatchDispatchable(Dispatchable $dispatchable, array $params = [])
    {
        return $dispatchable->dispatch($params);
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     * @param array $params
     * @return array
     * @throws Exception\MissingRequiredArgumentException
     */
    protected function buildArgsFromReflectionFunction(\ReflectionFunctionAbstract $reflection, array $params)
    {
        $args = [];

        foreach ($reflection->getParameters() as $param) {
            if (isset($params[$param->getName()])) {
                $args[] = $params[$param->getName()];
            } else if (!$param->isOptional()) {
                throw new Exception\MissingRequiredArgumentException($param->getName());
            }
        }

        return $args;
    }
}
