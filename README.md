# call_user_func

[call_user_func()](https://php.net/call_user_func) and [call_user_func_array()](https://php.net/call_user_func_array) are often mentioned as "slow". At some point I needed to know how by much this could impact processes involving very large amount of Callable calls.

## The problem

A Callable can be of many forms, either a string, an array a lambda or a Closure.
If you want to generically call a Callable, the easy path is to just call [call_user_func()](https://php.net/call_user_func) :
```php
$result = call_user_func($callable);
```

or [call_user_func_array()](https://php.net/call_user_func_array) :
```php
$result = call_user_func_array($callable);
```

This has the great advantage of simplicity, but unfortunately, it is slower than direct invocation.
This one liner also hides some complexity as each Callable type will need it's own invocation method. If we where to do it manually, we would use something like :

```php
if (is_string($callable)) {
    $callable = trim($callable, '\\');
    if (strpos($callable, '::')) {
        list($class, $method) = explode('::', $callable);
        $class::$method();
    } else {
        $callable();
    }
} else if (is_array($callable)) {
    $instance = current($callable);
    $method   = next($callable);
    $instance->$method();
} else {
    $callable();
}
```

Someone with more attention to estheticism could end up with a "smarter" solution using a simple "Closure Factory" :

```php
/**
 *
 * @param Callable $callable
 * @return \Closure
 */
function closureFactory(Callable $callable)
{
    if (is_string($callable)) {
        $callable = trim($callable, '\\');
        if (strpos($callable, '::')) {
            list($class, $method) = explode('::', $callable);
            return function () use ($class, $method) {
                return $class::$method();
            };
        } else {
            return function () use ($callable) {
                return $callable();
            };
        }
    } else if (is_array($callable)) {
        return function () use ($callable) {
            return $callable[0]->$callable[1]();
        };
    } else {
        return function () use ($callable) {
            return $callable();
        };
    }
}
```

wich would later allow things like :
```php
$closure = closureFactory($callable);
$result  = $closure();
```

In addition to being better organized, it allows reuse of the call for no additional cost, except reassigning in object context without php7 since calling :

```php
$instance->closure = closureFactory($callable);
$result            = $instance->closure();
```

will not work and :
```php
$instance->closure = closureFactory($callable);
$result            = ($instance->closure)();
```

will only work with php7. Bellow that you're stuck with :
```php
$instance->closure = closureFactory($callable);
$closure           = $instance->closure;
$result            = $closure();
```

wich brings a bit of overhead and complexity.

## Finding out

Exploring the options, I came up with a silly class, "Invoke", which wraps the Callable into a specialized class carrying the "fastest" invocation method. It seems insane at first because doing this involve wrapping the call into a class method, and in several cases, manipulating variables upon each call.

Invoke comes with a factory providing with the "best" instance for each Callable type :
```php
$Invoker = InvokeFactory::create($callable);
// ...
$Invoker->exec($param);
```

### Benchmarking

Of course, benchmarking does not tell everything and some benchmarks may even fail to prove the truth. In this case, I just timed the time taken to execute a number of calls of each recipe, and averaged over another number. Default for each test is 100 000 iterations averaged over 10 consecutive run (of 100k iteration each).
It's not perfect by nature, as many thing happens in a modern computer, but you can use bigger number to tend to better results.

If you wish to try, clone the repo and run
```bash
$ composer install --dev
```

Then the benchmark can be run using the ./bench script :
```bash
$ php bench
```

You can additionally set the number of iteration and average rounds :
```bash
$ php bench --help
bench usage
no options  :   run with default iteration (100 000) and default average rounds (10)
options     :
    -i=[0-9]    Number of iterations. Each test will run this many time
    -a=[0-9]    Compute an average over this many test. Each test will execute
                execute all its iterations this many time.
```

The idea is to compare each case with various ways of calling the Callable. Since the primary goal was to compare invocation times, the dummy function/method/static/lambda/closure are all following the same synopsis :
```php
function ($param) {
    return $param;
}
```

As the first results started to show, I added an even sillier test case which does the same thing as Invoke except it ends up calling call_user_func() instead of trying to be efficient. The idea behind it is to get an estimate of Invoke own overhead, since :
> invoke_time ~= invoke_overhead + recipe_exec_time

which when :
> recipe_exec_time ~= call_user_func_time

tells us
> invoke_overhead ~= invoke_time - call_user_func_time

Again, it's not math, it's benchmarking ^^

I ran test against both closure factory and assigned closure factory to measure the cost of closure assignment at run-time.

### Callable tested
With `$param` explicitly set to null before benchmark starts
* Instance

    ```php
    $instance = [$instance, 'method'];
    ```
* Static

    ```php
    $static = 'ClassName::method';
    ```
* Function

    ```php
    $function = 'functionName';
    ```
* Lambda

    ```php
    $lambda = function($param) {
         return $param;
    };
    ```
* Closure

    With `$use` explicitly set to null before benchmark starts
    ```php
    $closure = function($param) use ($use) {
        return $param;
    };
    ```

### Invocation tested
* call_user_func

    ```php
    // in test loop
    call_user_func($callable, $param);
    ```
* call_user_func_array

    ```php
    // in test loop
    call_user_func_array($callable, $arrayParam);
    ```
* directFunction

    ```php
    // in test loop
    fucntionName($param);
    ```
* directStatic

    ```php
    // in test loop
    ClassName::method($param);
    ```
* directInstance

    ```php
    // in test loop
    $instance->method($param);
    ```
* directLambda

    ```php
    // in test loop
    $lambda($param);
    ```
* directClosure

    ```php
    // in test loop
    $closure($param);
    ```
* ClosureFactory

    ```php
    // before test loop
    $closure = ClosureFactory::create($callable);
    // in test loop
    $closure($param);
    ```
* assignedClosureFactory

    ```php
    // before test loop
    $closure = ClosureFactory::create($callable);
    // in test loop
    $call = $closure;
    $call($param);
    ```
* directImplementation

    ```php
    // in test loop
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
    ```
* Invoke

    ```php
    // before test loop
    $instance = InvokeFactory::create($callable);
    // in test loop
    $instance->execOneArg($param);
    ```
* InvokeCallUserFunc

    ```php
    // before test loop
    $instance = new InvokeCallUserFunc($callable);
    // in test loop
    $instance->execOneArg($param);
    ```

## Conclusion

First thing to note is that call_user_func() has been improved a lot with php7. It's about 3x faster average with 7.1.2 compared with 5.6.30.
With 5.6.30, call_user_func almost always looses against Invoke, which in itself is interesting, especially if we evaluate Invoke's own overhead comparing with InvokeCallUserFunc case.
call_user_func_array() is always slower than call_user_func(), which is not a surprise, but again, it is much slower with 5.6.30.

Of course, if you think about real world scenario, if 60% slower is significant, looking at timings show we're talking about fractions of a second every million call, with each million call costing around half a second.
I can't think of many use cases where one would need to call millions of functions to build a web page, and few background process would actually loose so much with this 0.3 second lost every million call.

So as a practical conclusion, using call_user_func() is perfectly sane, even with php 5.6.30 (I did not tested bellow that).

Analyzing further, some interesting things to note are :
* The few ifs of a direct implementation are costing too much
* closure factory is surprisingly slow
* assigned closure factory is as expected a bit slower than simple closure factory, but it's not very significant
* php7 is a lot faster, with a bit more ram usage

## Results
Test ran on win10 with Intel(R) Core(TM) i7-2600K CPU @ 3.40GHz

### PHP 5.6.30
```bash
$ php bench
Benchmarking call_user_func
Iterations: 100 000
Averaged over: 10
PHP 5.6.30 (cli) (built: Jan 18 2017 19:47:28)
Copyright (c) 1997-2016 The PHP Group
Zend Engine v2.6.0, Copyright (c) 1998-2016 Zend Technologies
Windows NT 10.0 build 14393 (Windows 10) AMD64

instance ~ [$instance, 'method']
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directInstance         | 0.0116   | -0.0168   | -59.13 |
| call_user_func         | 0.0284   |           |        |
| Invoke                 | 0.0301   | +0.0017   | +6.11  |
| ClosureFactory         | 0.0370   | +0.0086   | +30.13 |
| directImplementation   | 0.0371   | +0.0087   | +30.48 |
| assignedClosureFactory | 0.0377   | +0.0093   | +32.83 |
| call_user_func_array   | 0.0393   | +0.0109   | +38.29 |
| InvokeCallUserFunc     | 0.0395   | +0.0111   | +39.26 |
+------------------------+----------+-----------+--------+

static ~ 'Class::method'
+------------------------+----------+-----------+---------+
| Invocation             | Time (s) | Delta (s) | %       |
+------------------------+----------+-----------+---------+
| directStatic           | 0.0098   | -0.0270   | -73.38  |
| Invoke                 | 0.0331   | -0.0037   | -10.03  |
| call_user_func         | 0.0367   |           |         |
| ClosureFactory         | 0.0396   | +0.0029   | +7.84   |
| assignedClosureFactory | 0.0400   | +0.0033   | +8.99   |
| call_user_func_array   | 0.0463   | +0.0095   | +25.97  |
| InvokeCallUserFunc     | 0.0496   | +0.0129   | +35.02  |
| directImplementation   | 0.0942   | +0.0575   | +156.46 |
+------------------------+----------+-----------+---------+

function ~ 'function'
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directFunction         | 0.0089   | -0.0230   | -72.03 |
| Invoke                 | 0.0261   | -0.0058   | -18.25 |
| ClosureFactory         | 0.0281   | -0.0039   | -12.15 |
| assignedClosureFactory | 0.0296   | -0.0024   | -7.53  |
| call_user_func         | 0.0320   |           |        |
| call_user_func_array   | 0.0416   | +0.0097   | +30.23 |
| InvokeCallUserFunc     | 0.0442   | +0.0122   | +38.31 |
| directImplementation   | 0.0521   | +0.0201   | +62.96 |
+------------------------+----------+-----------+--------+

lambda ~ function($param) { return $param }
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directLambda           | 0.0109   | -0.0135   | -55.15 |
| Invoke                 | 0.0226   | -0.0018   | -7.30  |
| ClosureFactory         | 0.0243   | -0.0001   | -0.40  |
| call_user_func         | 0.0244   |           |        |
| directImplementation   | 0.0251   | +0.0007   | +2.91  |
| assignedClosureFactory | 0.0263   | +0.0019   | +7.59  |
| call_user_func_array   | 0.0342   | +0.0098   | +40.09 |
| InvokeCallUserFunc     | 0.0356   | +0.0112   | +45.93 |
+------------------------+----------+-----------+--------+

closure ~ function($param) use($use) { return $param }
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directClosure          | 0.0150   | -0.0135   | -47.51 |
| call_user_func         | 0.0285   |           |        |
| ClosureFactory         | 0.0288   | +0.0003   | +1.20  |
| Invoke                 | 0.0289   | +0.0004   | +1.31  |
| directImplementation   | 0.0289   | +0.0004   | +1.50  |
| assignedClosureFactory | 0.0303   | +0.0019   | +6.50  |
| call_user_func_array   | 0.0381   | +0.0097   | +33.91 |
| InvokeCallUserFunc     | 0.0398   | +0.0113   | +39.60 |
+------------------------+----------+-----------+--------+

Overall Average
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directFunction         | 0.0089   | -0.0211   | -70.19 |
| directStatic           | 0.0098   | -0.0202   | -67.39 |
| directLambda           | 0.0109   | -0.0191   | -63.52 |
| directInstance         | 0.0116   | -0.0184   | -61.31 |
| directClosure          | 0.0150   | -0.0150   | -50.15 |
| Invoke                 | 0.0282   | -0.0018   | -6.13  |
| call_user_func         | 0.0300   |           |        |
| ClosureFactory         | 0.0316   | +0.0016   | +5.20  |
| assignedClosureFactory | 0.0328   | +0.0028   | +9.28  |
| call_user_func_array   | 0.0399   | +0.0099   | +33.02 |
| InvokeCallUserFunc     | 0.0418   | +0.0118   | +39.17 |
| directImplementation   | 0.0475   | +0.0175   | +58.28 |
+------------------------+----------+-----------+--------+

Time: 13.83 seconds, Memory: 1.00MB
```

### PHP 7.1.2
```bash
$ php bench
Benchmarking call_user_func
Iterations: 100 000
Averaged over: 10
PHP 7.1.2 (cli) (built: Feb 14 2017 21:24:45) ( NTS MSVC14
Copyright (c) 1997-2017 The PHP Group
Zend Engine v3.1.0, Copyright (c) 1998-2017 Zend Technologi
Windows NT 10.0 build 14393 (Windows 10) AMD64

instance ~ [$instance, 'method']
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directInstance         | 0.0058   | -0.0080   | -58.11 |
| call_user_func         | 0.0138   |           |        |
| call_user_func_array   | 0.0148   | +0.0009   | +6.74  |
| directImplementation   | 0.0158   | +0.0019   | +13.93 |
| Invoke                 | 0.0188   | +0.0050   | +36.06 |
| ClosureFactory         | 0.0215   | +0.0076   | +55.29 |
| assignedClosureFactory | 0.0222   | +0.0084   | +60.48 |
| InvokeCallUserFunc     | 0.0254   | +0.0115   | +83.44 |
+------------------------+----------+-----------+--------+

static ~ 'Class::method'
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directStatic           | 0.0050   | -0.0236   | -82.55 |
| Invoke                 | 0.0273   | -0.0013   | -4.54  |
| ClosureFactory         | 0.0285   | -0.0001   | -0.43  |
| call_user_func         | 0.0286   |           |        |
| call_user_func_array   | 0.0295   | +0.0009   | +3.03  |
| assignedClosureFactory | 0.0296   | +0.0010   | +3.59  |
| InvokeCallUserFunc     | 0.0424   | +0.0138   | +48.27 |
| directImplementation   | 0.0534   | +0.0248   | +86.82 |
+------------------------+----------+-----------+--------+

function ~ 'function'
+------------------------+----------+-----------+---------+
| Invocation             | Time (s) | Delta (s) | %       |
+------------------------+----------+-----------+---------+
| directFunction         | 0.0043   | -0.0072   | -62.59  |
| call_user_func         | 0.0115   |           |         |
| call_user_func_array   | 0.0123   | +0.0007   | +6.42   |
| Invoke                 | 0.0188   | +0.0073   | +63.31  |
| ClosureFactory         | 0.0194   | +0.0078   | +68.02  |
| assignedClosureFactory | 0.0206   | +0.0091   | +79.05  |
| InvokeCallUserFunc     | 0.0235   | +0.0120   | +104.16 |
| directImplementation   | 0.0268   | +0.0153   | +132.95 |
+------------------------+----------+-----------+---------+

lambda ~ function($param) { return $param }
+------------------------+----------+-----------+---------+
| Invocation             | Time (s) | Delta (s) | %       |
+------------------------+----------+-----------+---------+
| directLambda           | 0.0063   | -0.0004   | -6.35   |
| call_user_func         | 0.0067   |           |         |
| call_user_func_array   | 0.0076   | +0.0008   | +12.17  |
| directImplementation   | 0.0091   | +0.0023   | +34.19  |
| Invoke                 | 0.0133   | +0.0066   | +97.79  |
| ClosureFactory         | 0.0156   | +0.0088   | +131.14 |
| assignedClosureFactory | 0.0172   | +0.0105   | +155.52 |
| InvokeCallUserFunc     | 0.0187   | +0.0120   | +177.78 |
+------------------------+----------+-----------+---------+

closure ~ function($param) use($use) { return $param }
+------------------------+----------+-----------+---------+
| Invocation             | Time (s) | Delta (s) | %       |
+------------------------+----------+-----------+---------+
| directClosure          | 0.0081   | -0.0005   | -6.31   |
| call_user_func         | 0.0086   |           |         |
| call_user_func_array   | 0.0093   | +0.0007   | +8.02   |
| directImplementation   | 0.0111   | +0.0024   | +28.31  |
| Invoke                 | 0.0151   | +0.0064   | +74.18  |
| ClosureFactory         | 0.0187   | +0.0101   | +116.39 |
| assignedClosureFactory | 0.0197   | +0.0110   | +127.78 |
| InvokeCallUserFunc     | 0.0222   | +0.0135   | +156.45 |
+------------------------+----------+-----------+---------+

Overall Average
+------------------------+----------+-----------+--------+
| Invocation             | Time (s) | Delta (s) | %      |
+------------------------+----------+-----------+--------+
| directFunction         | 0.0043   | -0.0096   | -68.92 |
| directStatic           | 0.0050   | -0.0089   | -64.04 |
| directInstance         | 0.0058   | -0.0081   | -58.22 |
| directLambda           | 0.0063   | -0.0075   | -54.44 |
| directClosure          | 0.0081   | -0.0058   | -41.57 |
| call_user_func         | 0.0139   |           |        |
| call_user_func_array   | 0.0147   | +0.0008   | +5.84  |
| Invoke                 | 0.0187   | +0.0048   | +34.61 |
| ClosureFactory         | 0.0207   | +0.0069   | +49.43 |
| assignedClosureFactory | 0.0219   | +0.0080   | +57.75 |
| directImplementation   | 0.0232   | +0.0094   | +67.53 |
| InvokeCallUserFunc     | 0.0264   | +0.0126   | +90.67 |
+------------------------+----------+-----------+--------+

Time: 7.69 seconds, Memory: 2.00MB
```
