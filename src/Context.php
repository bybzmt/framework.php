<?php
namespace Bybzmt\Framework;

/**
 * 环境上下对像
 */
class Context
{
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
        if (!isset($this->components[$name])) {
            //$args = array_merge(explode(":", $name), $args);
            $this->components[$name] = $this->init(strtr($name, '.', '\\'), ...$args);
        }

        return $this->components[$name];
    }

    //设置组件
    public function set(string $name, $val)
    {
        $this->components[$name] = $val;
    }

    /**
     * 初始化一个数据行对像
     */
    public function initRow(string $name, array $row)
    {
        return $this->init("Row\\" . strtr($name, '.', '\\'), $row);
    }

    /**
     * 得到缓存对像
     */
    public function getCache(string $name, string $id='', ...$args)
    {
        $cache_id = $name . $id;

        if (!isset($this->caches[$cache_id])) {
            $this->caches[$cache_id] = $this->init("Cache\\".$name, $id, ...$args);
        }

        return $this->caches[$cache_id];
    }

    ##############
    ## 数据加截 ##

    /**
     * 直接加载一个数据行对像
     */
    public function getRow(string $name, string $id)
    {
        $row = $this->get("Table.".$name)->get($id);

        return $row ? $this->initRow($name, $row) : false;
    }

    public function getRows(string $name, array $ids)
    {
        $rows = $this->get("Table.".$name)->gets($ids);

        $obj = array();
        foreach ($rows as $row) {
            $obj[] = $this->initRow($name, $row);
        }
        return $obj;
    }

    /**
     * 惰性加载一个数据行对像
     */
    public function getLazyRow(string $name, string $id)
    {
        return new LazyRow($this, $name, $id);
    }

    public function getLazyRows(string $name, array $ids)
    {
        $obj = array();
        foreach ($ids as $id) {
            $obj[] = new LazyRow($this, $name, $id);
        }
        return $obj;
    }

}
