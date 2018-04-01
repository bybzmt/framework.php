<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Helper;
use Bybzmt\Framework\Config;
use Bybzmt\Router\Reverse;

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

    //创建url
    public function mkUrl(string $action, array $params=array(), bool $https=false)
    {
        $uri = $this->_ctx->module->getReverse()->buildUri($action, $params);

        $host = Config::get('host.' . $this->_ctx->moduleName);

        return ($https ? 'https://' : 'http://') . $host . $uri;
    }

}
