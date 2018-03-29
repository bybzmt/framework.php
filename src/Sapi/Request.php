<?php
namespace Bybzmt\Framework\Sapi;

/**
 * 请求对像
 */
abstract class Request
{
    public $header;
    public $server;
    public $get;
    public $post;
    public $cookie;
    public $file;

    abstract public function rawContent();

    abstract public function getData();
}
