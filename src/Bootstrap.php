<?php
namespace Bybzmt\Blog\Common;

abstract class Bootstrap
{
    public function __construct()
    {
        set_error_handler(array($this, 'exception_error_handler'));
    }

    public function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    abstract public function run($request, $response);
}
