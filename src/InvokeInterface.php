<?php

namespace fab2s\Invoke;

/**
 * Interface InvokeInterface
 * @package fab2s\Invoke
 */
interface InvokeInterface
{
    /**
     * exec the Callable with no arg
     *
     * @return mixed
     */
    public function exec();

    /**
     * exec the Callable with one arg
     *
     * @param mixed $param
     * @return mixed
     */
    public function execOneArg($param);

    /**
     * exec the Callable with two arg
     *
     * @param mixed $param1
     * @param mixed $param2
     * @return mixed
     */
    public function execTwoArg($param1, $param2);

    /**
     * exec the Callable with three arg
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @return mixed
     */
    public function execThreeArg($param1, $param2, $param3);
}
