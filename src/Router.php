<?php
namespace Bybzmt\Framework;

use Bybzmt\Framework\Config;

use Bybzmt\Router\Router as PRouter;

abstract class Router extends PRouter
{
    public function __construct($context)
    {
        $this->_init();
    }

    abstract protected function _init();

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


}
