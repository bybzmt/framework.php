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
    //取得IP, 方便根据环境统一调整
    public function getIP()
    {
        if (Config::get('x_forwarded_for')) {
            if (isset($this->_ctx->request->header['x_forwarded_for'])) {
                list($ip) = explode(',', $this->_ctx->request->header['x_forwarded_for'], 2);
                if ($ip) {
                    return $ip;
                }
            }
        }

        return $this->_ctx->request->server['remote_addr'];
    }

    //创建url
    public function mkUrl(string $action, array $params=array(), bool $https=false)
    {
        $uri = $this->_ctx->module->getReverse()->buildUri($action, $params);

        $host = Config::get('host.' . $this->_ctx->module->name);

        return ($https ? 'https://' : 'http://') . $host . $uri;
    }

}
