<?php
namespace Bybzmt\Blog\Common;

class Config
{
    static protected $data;

    static protected function init()
    {
        if (!self::$data) {
            self::$data = require CONFIG_PATH . '/config.php';
        }
    }

    static public function get(string $keys)
    {
        self::init();

        $tmp = self::$data;
        $keys = explode('.', $keys);

        foreach ($keys as $key) {
            if (!isset($tmp[$key])) {
                return null;
            }
            $tmp = $tmp[$key];
        }

        return $tmp;
    }

    static public function set(string $keys, $value)
    {
        self::init();
        $tmp = &self::$data;

        $keys = explode('.', $keys);

        foreach ($keys as $key) {
            $tmp = &$tmp[$key];
        }

        $tmp = $vlaue;
    }

}
