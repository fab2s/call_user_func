<?php

namespace fab2s\Invoke;

/**
 * Class InvokeFactory
 * @package fab2s\Invoke
 */
class InvokeFactory
{
    /**
     *
     * @param Callable $callable
     * @return InvokeInterface
     */
    public static function create(Callable $callable)
    {
        if (is_string($callable)) {
            $callable = trim($callable, '\\');
            if (strpos($callable, '::')) {
                list($class, $method) = explode('::', $callable);
                return new InvokeStatic($class, $method);
            } else {
                return new InvokeFunction($callable);
            }
        } else if (is_array($callable)) {
            return new InvokeInstance(current($callable), next($callable));
        } else {
            return new InvokeClosure($callable);
        }
    }
}
