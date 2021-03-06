<?php
namespace Bybzmt\Framework\Sapi\Phpfpm;

/**
 * 请求对像
 */
class Request
{
    public $header;
    public $server;
    public $get;
    public $post;
    public $cookie;
    public $files;

    public function __construct()
    {
        $this->header = new LikeSwooleHTTPRequestHeader();
        $this->server = new LikeSwooleHTTPRequestServer();
        $this->get = $_GET;
        $this->post = $_POST;
        $this->cookie = $_COOKIE;
        $this->file = $_FILES;
    }

    public function rawContent()
    {
        return file_get_contents('php://input');
    }

    public function getData()
    {
        return "";
    }

}
