<?php
namespace Bybzmt\Framework;

use ReflectionObject;

/**
 * 环境上下对像
 */
class Context
{
    //模块对像
    public $module;

    //请求对像
    public $request;

    //响应对像
    public $response;

    //组件
    public $components = array();

    //初始化组件
    public function initComponent(string $name, ...$args)
    {
        $class = __NAMESPACE__ ."\\". $name;

        if (!class_exists($class)) {
            $trys = [];

            $tmp = new ReflectionObject($this);
            do {
                $trys[] = $tmp->getNamespaceName() . "\\" . $name;
            } while($tmp = $tmp->getParentClass());

            throw new Exception("Component: $name Not Found. trys: ".implode(", ", $trys));
        }

        return new $class($this, ...$args);
    }

    //得到组件
    public function getComponent(string $name)
    {
        if (!isset($this->components[$name])) {
            $this->components[$name] = $this->initComponent($name);
        }

        return $this->components[$name];
    }

    public function now()
    {
        return $this->request->server['request_time'];
    }

}
