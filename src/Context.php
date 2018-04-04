<?php
namespace Bybzmt\Framework;

/**
 * 环境上下对像
 */
class Context
{
    public $moduleName;
    public $module;

    //请求对像
    public $request;

    //响应对像
    public $response;

    //组件
    public $components = array();

    //标记的批量加载
    public $lazyRow;

    //初始化组件
    public function initComponent(string $name, ...$args)
    {
        $class = __NAMESPACE__ ."\\". $name;
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

}
