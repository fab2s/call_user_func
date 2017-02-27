<?php

namespace fab2s\Invoke;

/**
 * Class InvokeInstance
 * @package fab2s\Invoke
 */
class InvokeInstance implements InvokeInterface
{
    /**
     *
     * @var object
     */
    protected $instance;

    /**
     *
     * @var string
     */
    protected $method;

    /**
     *
     * @param object $instance
     * @param string $method
     */
    public function __construct($instance, $method)
    {
        $this->instance = $instance;
        $this->method   = $method;
    }

    /**
     *
     * @return mixed
     */
    public function exec()
    {
        return $this->instance->{$this->method}();
    }

    /**
     *
     * @return mixed
     */
    public function execOneArg($param)
    {
        return $this->instance->{$this->method}($param);
    }

    /**
     *
     * @return mixed
     */
    public function execTwoArg($param1, $param2)
    {
        return $this->instance->{$this->method}($param1, $param2);
    }

    /**
     *
     * @return mixed
     */
    public function execThreeArg($param1, $param2, $param3)
    {
        return $this->instance->{$this->method}($param1, $param2, $param3);
    }
}
