<?php

function functionTest($param)
{
    return $param;
}

class InstanceTest
{
    public function methodTest($param)
    {
        return $param;
    }
}

class StaticTest
{
    public static function methodTest($param)
    {
        return $param;
    }
}

$lambdaTest = function ($param) {
    return $param;
};

$use = null;
$closureTest = function ($param) use ($use) {
    return $param;
};


