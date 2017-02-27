<?php

namespace fab2s\Invoke;

/**
 * Class InvokeCallUserFunc
 * Only used to demonstracte how incredibly slow is call_user_func
 * in ./bench
 * Using this class makes it possible to obtain an estimate of
 * Invoke overhead in comparison to a direct call
 *
 * Never instanciated from the InvokeFactory
 *
 * @package fab2s\Invoke
 */
class InvokeCallUserFunc implements InvokeInterface
{
    /**
     *
     * @var string
     */
    protected $calable;

    /**
     *
     * @param Callable $calable
     */
    public function __construct(Callable $calable)
    {
        $this->calable = $calable;
    }

    /**
     *
     * @return mixed
     */
    public function exec()
    {
        return call_user_func($this->calable);
    }

    /**
     *
     * @param mixed $param
     * @return mixed
     */
    public function execOneArg($param)
    {
        return call_user_func($this->calable, $param);
    }

    /**
     *
     * @return mixed
     */
    public function execTwoArg($param1, $param2)
    {
        return call_user_func($this->calable, $param1, $param2);
    }

    /**
     *
     * @return mixed
     */
    public function execThreeArg($param1, $param2, $param3)
    {
        return call_user_func($this->calable, $param1, $param2, $param3);
    }
}
