<?php

use fab2s\Invoke\InvokeFactory;
use fab2s\Invoke\ClosureFactory;
use fab2s\Invoke\InvokeCallUserFunc;

function benchDirectStatic($param)
{
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            StaticTest::methodTest($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchDirectInstance($param)
{
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    $instance = new InstanceTest;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $instance->methodTest($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchDirectFunction($param)
{
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            functionTest($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchDirectLambda($param)
{
    global $lambdaTest;
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $lambdaTest($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchDirectClosure($param)
{
    global $closureTest;
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $closureTest($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchInvoke($callable, $param)
{
    $Invoker     = InvokeFactory::create($callable);
    $avgTime     = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $Invoker->execOneArg($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchCall_user_func($callable, $param)
{
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            call_user_func($callable, $param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchCall_user_func_array($callable, $param)
{
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    $param = [$param];
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            call_user_func_array($callable, $param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchAssignedClosureFactory($callable, $param)
{
    $Invoker = ClosureFactory::create($callable);
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $call = $Invoker;
            $call($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchClosureFactory($callable, $param)
{
    $Invoker = ClosureFactory::create($callable);
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $Invoker($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchInvokeCallUserFunc($callable, $param)
{
    $invokeCallUserFunc = new InvokeCallUserFunc($callable);
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            $invokeCallUserFunc->execOneArg($param);
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}

function benchDirectImplementation($callable, $param)
{
    $avgTime = 0;
    $averageOver = AVG_OVER;
    $iterations  = ITERATIONS;
    for ($i = 0; $i < $averageOver; $i++) {
        PHP_Timer::start();
        for ($cnt = 1; $cnt <= $iterations; $cnt++) {
            if (is_string($callable)) {
                $callable = trim($callable, '\\');
                if (strpos($callable, '::')) {
                    list($class, $method) = explode('::', $callable);
                    $class::$method($param);
                } else {
                    $callable($param);
                }
            } else if (is_array($callable)) {
                $class = $callable[0];
                $method = $callable[1];
                $class->{$method}($param);
            } else {
                $callable($param);
            }
        }
        $avgTime += PHP_Timer::stop();
    }

    return $avgTime/$averageOver;
}