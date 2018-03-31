<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Helper;
use Memcached;

/**
 * 实用工具
 */
class Utils extends Helper
{
    //取得IP
    public function getIP()
    {
        //方便根据环境统一调整
        //return $this->_ctx->request->server['x_forwarded_for'];

        return $this->_ctx->request->server['remote_addr'];
    }

}
