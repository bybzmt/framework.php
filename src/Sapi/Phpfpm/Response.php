<?php
namespace Bybzmt\Blog\Common\Sapi\Phpfpm;

/**
 * 响应对像
 */
class Response
{
    public function __construct()
    {
        ob_start();
    }

    public function header($key, $val)
    {
        header("$key: $val");
    }

    public function cookie($key, $val= '', $expire = 0 , $path = '/', $domain  = '', $secure = false, $httponly = false)
    {
        setcookie($key, $val, $expire, $path, $domain, $secure, $httponly);
    }

    public function status($code)
    {
        http_response_code($code);
    }

    public function gzip($level = 1)
    {
        ob_start("ob_gzhandler");
    }

    public function write($data)
    {
        echo $data;
    }

    public function sendfile(string $filename, int $offset = 0, int $length = 0)
    {
        ob_end_flush();
        readfile($filename);
    }

    public function end($html)
    {
        if ($html) {
            echo $html;
        }
        ob_end_flush();
    }


}
