<?php

namespace fab2s\Invoke;

/**
 * Class JoinerFunction
 * @package fab2s\Invoke
 */
class InvokeFunction implements InvokeInterface
{
    /**
     *
     * @var string
     */
    protected $function;

    /**
     *
     * @param string $function
     */
    public function __construct($function)
    {
        $this->function = $function;
    }

    /**
     *
     * @return mixed
     */
    public function exec()
    {
        $function = $this->function;
        return $function();
    }

    /**
     *
     * @return mixed
     */
    public function execOneArg($param)
    {
        $function = $this->function;
        return $function($param);
    }

    /**
     *
     * @return mixed
     */
    public function execTwoArg($param1, $param2)
    {
        $function = $this->function;
        return $function($param1, $param2);
    }

    /**
     *
     * @return mixed
     */
    public function execThreeArg($param1, $param2, $param3)
    {
        $function = $this->function;
        return $function($param1, $param2, $param3);
    }
}
