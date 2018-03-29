<?php
namespace Bybzmt\Blog\Common;

use Bybzmt\Blog\Common\Config;

use Bybzmt\Router\Router as PRouter;

abstract class Router extends PRouter
{
    protected $_ctx;

    public function __construct($context)
    {
        $this->_ctx = $context;

        if (Config::get('routes_cached')) {
            parent::__construct($this->_restore());
        } else {
            $this->_init();
        }
    }

    abstract protected function _init();

    protected function _restore()
    {
        $file = ASSETS_PATH . '/compiled/' . str_replace('\\', '_', static::class) . '_routes.php';
        return require $file;
    }

    public function getMethod()
    {
        return $this->_ctx->request->server['request_method'];
    }

    public function getURI()
    {
        $uri = parse_url($this->_ctx->request->server['request_uri'], PHP_URL_PATH);

        $len = strlen($this->getBasePath());
        if ($len > 0) {
            $uri = substr($uri, $len);
        }

        return $uri;
    }

    protected function _parseClass($map)
    {
        static $names;
        if (!$names) {
            //根据子类的命名空间得到子类所在模块的命名空间
            $names = implode('\\', array_slice(explode('\\', static::class), 0, -1));
        }

        $str = str_replace($this->_separator_method, '\\', $map);

        $class = $names .'\\Controller\\'. $str;
        $method = 'execute';

        return array($class, $method);
    }

    protected function dispatch($func, array $params)
    {
        list($class, $method, $keys, $map) = $func;

        //映射参数到$_GET中去
        $this->_mapGET($params, $keys);

        if (!$this->_loadClass($class)) {
            throw new Exception("Dispatch '$map' Class:'$class' Not Exists");
        }

        $obj = new $class($this->_ctx);

        if (!method_exists($obj, $method)) {
            throw new Exception("Dispatch '$map' Method:'$class::$method' Not Exists");
        }

        return $obj->$method();
    }
}
