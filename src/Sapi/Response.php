<?php
namespace Bybzmt\Blog\Common\Sapi;

/**
 * 响应对像
 */
interface Response
{
    public function header($key, $val);

    public function cookie($key, $val='', $expire=0, $path='/', $domain='', $secure=false, $httponly=false);

    public function status($code);

    public function gzip($level=1);

    /**
     * 分次输出，最后必以无参数end()结束
     */
    public function write($data);

    /**
     * 输入内容，并关闭连连
     */
    public function end($html=null);

    /**
     * 输出文件，并关闭连接
     */
    public function sendfile(string $filename, int $offset=0, int $length=0);
}
