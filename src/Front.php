<?php
namespace Bybzmt\Framework;

use Bybzmt\Framework\Sapi\Phpfpm\Request;
use Bybzmt\Framework\Sapi\Phpfpm\Response;
use swoole_http_server;

/**
 * 前端控制器
 */
class Front
{
    private $_moduleMap;
    private $_modules = array();

    public function __construct(callable $module)
    {
        $this->_moduleMap = $module;

        set_error_handler(array($this, 'exception_error_handler'), error_reporting());
    }

    public function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    public function run()
    {
        if (PHP_SAPI == 'cli') {
            if ($this->isSwoole()) {
                return $this->swooleServer();
            } else {
                $_SERVER['HTTP_HOST'] = "#CLI";
            }
        }

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;

        $module = call_user_func($this->_moduleMap, $host);

        $request = new Request();
        $response = new Response();

        $module->run($request, $response);
    }

    protected function isSwoole()
    {
        return isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == "-d";
    }

    protected function swooleServer()
    {
        $listen = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;
        list($addr, $port) = explode(":", $listen .":");

        $http = new swoole_http_server($addr, $port);

        $http->on('request', function ($request, $response) {
            $host = isset($request->header['host']) ? $request->header['host'] : null;

            //此模式下Bootstrap对像是常驻内存的
            $module = isset($this->_modules[$host]) ? $this->_modules[$host] : $this->_modules[$host] = call_user_func($this->_moduleMap, $host);

            $module->run($request, $response);
        });

        $http->start();
    }

}
