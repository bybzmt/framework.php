<?php
namespace Bybzmt\Framework;

use Bybzmt\Router\Tool;
use Bybzmt\Router\Reverse;

/**
 * 模块启器 (Swoole模式下它是常驻内存的)动
 */
abstract class Bootstrap
{
    //模块名字 (必填)
    public $name;

    //路由
    protected $router;

    //反向路由
    protected $reverse;

    //得到上下文对像
    abstract public function getContext();

    //执行请求
    public function run($request, $response)
    {
        //初始化上下文对像
        $ctx = $this->getContext();
        $ctx->request = $request;
        $ctx->response = $response;

        //路由请求
        $method = $this->getMethod($request);
        $uri = $this->getUri($request);

        if (list($func, $params) = $this->getRouter()->match($method, $uri)) {
            list($obj, $method) = $this->preprocess($ctx, $func, $params);

            //执行控制器
            $obj->$method();
        } else {
            $this->default404($ctx);
        }
    }

    //得到反向路由
    public function getReverse()
    {
        if (!$this->reverse) {
            $tool = new Tool($this->getRouter()->getRoutes());
            $this->reverse = new Reverse($tool->convertReverse());
        }

        return $this->reverse;
    }

    //得到路由器
    protected function getRouter()
    {
        if (!$this->router) {
            $this->router = $this->getContext()->initComponent("Router");
        }

        return $this->router;
    }

    protected function getMethod($request)
    {
        return $request->server['request_method'];
    }

    protected function getURI($request)
    {
        list($uri) = explode('?', $request->server['request_uri'], 2);
        return $uri;
    }

    //映射参数到GET参数中去
    protected function _mapGET($ctx, array $params, array $keys)
    {
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

    //预处理
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
