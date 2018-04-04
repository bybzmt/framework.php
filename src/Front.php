<?php
namespace Bybzmt\Framework;

use Bybzmt\Framework\Sapi\Phpfpm\Request;
use Bybzmt\Framework\Sapi\Phpfpm\Response;
use swoole_http_server;
use swoole_lock;

class Front
{
    private $_moduleMap;
    private $_modules = array();
    private $_locker;

    public function __construct(callable $module)
    {
        $this->_moduleMap = $module;
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

    protected function swooleGetModule($host)
    {
        //加读锁
        $this->_locker->lock_read();
        if (isset($this->_modules[$host])) {
            $module = $this->_modules[$host];
            //解读锁
            $this->_locker->unlock();
        } else {
            //解读锁
            $this->_locker->unlock();

            //加写锁
            $this->_locker->lock();

            $module = call_user_func($this->_moduleMap, $host);
            $this->_modules[$host] = $module;

            //解写锁
            $this->_locker->unlock();
        }

        return $module;
    }

    protected function swooleServer()
    {
        $listen = isset($_SERVER['argv'][2]) ? $_SERVER['argv'][2] : null;
        list($addr, $port) = explode(":", $listen .":");

        $this->_locker = new swoole_lock(SWOOLE_RWLOCK);

        $http = new swoole_http_server($addr, $port);

        $http->on('request', function ($request, $response) {
            $host = isset($request->header['host']) ? $request->header['host'] : null;

            $module = $this->swooleGetModule($host);

            $module->run($request, $response);
        });

        $http->start();
    }

}
