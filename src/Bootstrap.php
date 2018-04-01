<?php
namespace Bybzmt\Framework;

abstract class Bootstrap
{
    public function __construct()
    {
        set_error_handler(array($this, 'exception_error_handler'), error_reporting());
    }

    public function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    //得到上下文对像
    abstract public function getContext();

    //执行请求
    abstract public function run($request, $response);
}
