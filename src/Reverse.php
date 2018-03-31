<?php
namespace Bybzmt\Framework;

use Bybzmt\Framework\Config;
use Bybzmt\Router\Reverse as PReverse;

abstract class Reverse extends PReverse
{
    public function __construct()
    {
        parent::__construct(self::initData());
    }

    static protected function initData()
    {
        if (Config::get('routes_cached')) {
            $file = ASSETS_PATH . '/compiled/' . str_replace('\\', '_', static::class) . '_reverse.php';
            return require $file;
        } else {
            $class = substr(static::class, 0, strrpos(static::class, '\\')) . '\\Router';

            $router = new $class(null);
            $tool = new \Bybzmt\Router\Tool($router->getRoutes());
            return $tool->convertReverse();
        }
    }

    abstract public function mkUrl(string $func, array $params=array(), bool $https=false);
}
