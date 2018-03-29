<?php
namespace Bybzmt\Blog\Common\Sapi\Phpfpm;

/**
 * 请求对像
 */
class LikeSwooleHTTPRequestHeader implements \ArrayAccess, \Iterator
{
    public function offsetSet($offset, $value)
    {
        //禁止修改
    }

    public function offsetUnset($offset)
    {
        //禁止修改
    }

    public function offsetExists($offset)
    {
        $offset = strtoupper("HTTP_".$offset);
        return isset($_SERVER[$offset]);
    }

    public function offsetGet($offset)
    {
        $offset = strtoupper("HTTP_".$offset);
        return isset($_SERVER[$offset]) ? $_SERVER[$offset] : null;
    }

    public function current()
    {
        return current($_SERVER);
    }

    public function key()
    {
        return strtolower(substr(key($_SERVER), 5));
    }

    public function next()
    {
        //直到找到HTTP_开头的数组
        while (true) {
            next($_SERVER);
            $key = key($_SERVER);
            if ($key === null) {
                break;
            }
            if (substr($key, 0, 5) == "HTTP_") {
                break;
            }
        }
    }

    public function rewind()
    {
        reset($_SERVER);
    }

    public function valid()
    {
        return key($_SERVER) !== null;
    }




}
