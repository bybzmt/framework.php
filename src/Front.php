<?php
namespace Bybzmt\Blog\Common;

use Bybzmt\Blog\Common\Sapi\Phpfpm\Request;
use Bybzmt\Blog\Common\Sapi\Phpfpm\Response;
use swoole_http_server;

class Front
{
    private $_module;

    public function __construct(callable $module)
    {
        $this->_module = $module;
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

        $module = call_user_func($this->_module, $host);

        $request = new Request();
        $response = new Response();

        $module->run($request, $response);
    }

    public function isSwoole()
    {
        return isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == "-d";
    }

    public function swooleServer()
    {
        $listen = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;
        list($addr, $port) = explode(":", $listen .":");

        $http = new swoole_http_server($addr, $port);

        $http->on('request', function ($request, $response) {
            $host = isset($request->header['host']) ? $request->header['host'] : null;

            $module = call_user_func($this->_module, $host);

            $module->run($request, $response);
        });

        $http->start();
    }

}
