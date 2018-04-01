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
    public function init(string $name, ...$args)
    {
        $class = __NAMESPACE__ ."\\". $name;
        return new $class($this, ...$args);
    }

    //得到组件
    public function get(string $name, ...$args)
    {
        $id = $args ? $name.':'.implode(',',$args) : $name;

        if (!isset($this->components[$id])) {
            $this->components[$id] = $this->init(strtr($name, '.', '\\'), ...$args);
        }

        return $this->components[$id];
    }

}
