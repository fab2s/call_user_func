<?php

namespace fab2s\Invoke;

/**
 * Class InvokeStatic
 * @package fab2s\Invoke
 */
class InvokeStatic implements InvokeInterface
{
    /**
     *
     * @var string
     */
    protected $class;

    /**
     *
     * @var string
     */
    protected $method;

    /**
     *
     * @param string $class
     * @param string $method
     */
    public function __construct($class, $method)
    {
        $this->class  = $class;
        $this->method = $method;
    }

    /**
     *
     * @return mixed
     */
    public function exec()
    {
        $class = $this->class;
        return $class::{$this->method}();
    }

    /**
     *
     * @return mixed
     */
    public function execOneArg($param)
    {
        $class = $this->class;
        return $class::{$this->method}($param);
    }

    /**
     *
     * @return mixed
     */
    public function execTwoArg($param1, $param2)
    {
        $class = $this->class;
        return $class::{$this->method}($param1, $param2);
    }

    /**
     *
     * @return mixed
     */
    public function execThreeArg($param1, $param2, $param3)
    {
        $class = $this->class;
        return $class::{$this->method}($param1, $param2, $param3);
    }
}
