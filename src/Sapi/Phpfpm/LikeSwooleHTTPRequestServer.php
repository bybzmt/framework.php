<?php
namespace Bybzmt\Blog\Common\Sapi\Phpfpm;

/**
 * 请求对像
 */
class LikeSwooleHTTPRequestServer implements \ArrayAccess, \Iterator
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
        $offset = strtoupper($offset);
        return isset($_SERVER[$offset]);
    }

    public function offsetGet($offset)
    {
        $offset = strtoupper($offset);
        return isset($_SERVER[$offset]) ? $_SERVER[$offset] : null;
    }

    public function current()
    {
        return current($_SERVER);
    }

    public function key()
    {
        return strtolower(key($_SERVER));
    }

    public function next()
    {
        next($_SERVER);
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
