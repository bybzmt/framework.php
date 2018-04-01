<?php
namespace Bybzmt\Framework;

use Bybzmt\Router\Tool;
use Bybzmt\Router\Reverse;

abstract class Bootstrap
{
    //路由
    protected $router;

    //反向路由
    protected $reverse;

    public function __construct()
    {
        set_error_handler(array($this, 'exception_error_handler'), error_reporting());
    }

    //得到上下文对像
    abstract public function getContext();

    public function exception_error_handler($errno, $errstr, $errfile, $errline)
    {
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    //执行请求
    public function run($request, $response)
    {
        $ctx = $this->getContext();
        $ctx->request = $request;
        $ctx->response = $response;

        $method = $this->getMethod($request);
        $uri = $this->getUri($request);

        if (list($func, $params) = $this->getRouter()->match($method, $uri)) {
            list($obj, $method) = $this->preprocess($ctx, $func, $params);

            $obj->$method();
        } else {
            $this->default404($ctx);
        }
    }

    public function getReverse()
    {
        if (!$this->reverse) {
            $tool = new Tool($this->getRouter()->getRoutes());
            $this->reverse = new Reverse($tool->convertReverse());
        }

        return $this->reverse;
    }

    protected function getRouter()
    {
        if (!$this->router) {
            $this->router = $this->getContext()->init("Router");
        }

        return $this->router;
    }

    protected function getMethod($request)
    {
        return $request->server['request_method'];
    }

    protected function getURI($request)
    {
        $uri = parse_url($request->server['request_uri'], PHP_URL_PATH);
        return $uri;
    }

    protected function _mapGET($ctx, array $params, array $keys)
    {
        //映射参数到$_GET中去
        foreach ($params as $i => $param) {
            if (isset($keys[$i])) {
                list($prefix, $key) = $keys[$i];

                if ($prefix) {
                    //去除可选参数前缀
                    $ctx->request->get[$key] = substr($param, strlen($prefix));
                } else {
                    $ctx->request->get[$key] = $param;
                }
            }
        }
    }

    protected function preprocess($ctx, $func, array $params)
    {
        list($class, $method, $keys, $map) = $func;

        //映射参数到$_GET中去
        $this->_mapGET($ctx, $params, $keys);

        if (!class_exists($class)) {
            throw new Exception("Dispatch '$map' Class:'$class' Not Exists");
        }

        $obj = new $class($ctx);

        if (!method_exists($obj, $method)) {
            throw new Exception("Dispatch '$map' Method:'$class::$method' Not Exists");
        }

        return array($obj, $method);
    }

    protected function default404($ctx)
    {
        $ctx->response->status(404);
        $ctx->response->end("Web 404 page not found\n");
    }

}
