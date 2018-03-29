<?php
namespace Bybzmt\Framework\Swoole;

use Bybzmt\Framework\Sapi\Phpfpm\Request as Base;
use swoole_http_request;

/**
 * 请求对像（只作文档使用，实际只使用swoole_http_request）
 */
class Request extends swoole_http_request implements Base
{
}
