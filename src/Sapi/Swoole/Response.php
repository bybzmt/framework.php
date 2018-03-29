<?php
namespace Bybzmt\Framework\Swoole;

use Bybzmt\Framework\Sapi\Phpfpm\Response as Base;
use swoole_http_response;

/**
 * 响应对像（只作文档使用，实际只使用swoole_http_response）
 */
class Response extends swoole_http_response implements Base
{
}
