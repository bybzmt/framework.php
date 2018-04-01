<?php
namespace Bybzmt\Framework;

//根组件trait实现,有时候不方便用继承
trait ComponentTrait
{
    public function __debugInfo()
    {
        //防止反复打印上下文对像
        $attr = get_object_vars($this);
        unset($attr['_ctx']);
        return $attr;
    }

    //直接加载一个数据行对像
    protected function getRow(string $name, string $id)
    {
        $row = $this->getTable($name)->get($id);

        return $row ? $this->_ctx->init("Row\\".$name, $row) : false;
    }

    //批量加载数据行对像
    protected function getRows(string $name, array $ids)
    {
        $rows = $this->getTable($name)->gets($ids);

        $obj = array();
        foreach ($rows as $row) {
            $obj[] = $this->_ctx->init("Row\\".$name, $row);
        }
        return $obj;
    }

    //惰性加载一个数据行对像
    protected function getLazyRow(string $name, string $id)
    {
        return new LazyRow($this->_ctx, $name, $id);
    }

    //批量懒加载
    protected function getLazyRows(string $name, array $ids)
    {
        $obj = array();
        foreach ($ids as $id) {
            $obj[] = new LazyRow($this->_ctx, $name, $id);
        }
        return $obj;
    }

    //得到缓存组件
    protected function getCache(string $name, $id='')
    {
        return $this->_ctx->get("Cache\\".$name, $id);
    }

    //加载表组件
    protected function getTable($name)
    {
        return $this->_ctx->get("Table\\".$name);
    }

    //加载组手组件
    protected function getHelper($name)
    {
        return $this->_ctx->get("Helper\\".$name);
    }

    //加载服务组件
    protected function getService($name)
    {
        return $this->_ctx->get("Service\\".$name);
    }

}

