<?php

namespace fab2s\Invoke;

/**
 * Class ClosureFactory
 * @package fab2s\Invoke
 */
class ClosureFactory
{
    /**
     *
     * @param Callable $callable
     * @return \Closure
     */
    public static function create(Callable $callable)
    {
        if (is_string($callable)) {
            $callable = trim($callable, '\\');
            if (strpos($callable, '::')) {
                list($class, $method) = explode('::', $callable);
                return function ($param) use ($class, $method) {
                    return $class::$method($param);
                };
            } else {
                return function ($param) use ($callable) {
                    return $callable($param);
                };
            }
        } else if (is_array($callable)) {
            reset($callable);
            $instance = current($callable);
            $method   = next($callable);
            return function ($param) use ($instance, $method) {
                return $instance->$method($param);
            };
        } else {
            return function ($param) use ($callable) {
                return $callable($param);
            };
        }
    }
}
