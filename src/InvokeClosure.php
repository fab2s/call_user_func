<?php

namespace fab2s\Invoke;

/**
 * Class InvokeClosure
 * @package fab2s\Invoke
 */
class InvokeClosure implements InvokeInterface
{
    /**
     *
     * @var \Closure
     */
    protected $closure;

    /**
     *
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     *
     * @return mixed
     */
    public function exec()
    {
        $closure = $this->closure;
        return $closure();
    }

    /**
     *
     * @return mixed
     */
    public function execOneArg($param)
    {
        $closure = $this->closure;
        return $closure($param);
    }

    /**
     *
     * @return mixed
     */
    public function execTwoArg($param1, $param2)
    {
        $closure = $this->closure;
        return $closure($param1, $param2);
    }

    /**
     *
     * @return mixed
     */
    public function execThreeArg($param1, $param2, $param3)
    {
        $closure = $this->closure;
        return $closure($param1, $param2, $param3);
    }
}
