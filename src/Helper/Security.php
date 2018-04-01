<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Helper;
use Memcached;

/**
 * 安全
 */
class Security extends Helper
{
    protected $_cachekey = __CLASS__;
    protected $_expiration = 60*60*12;
    protected $_ip;

    protected $_hold;
    protected $_cas;
    protected $_value;
    protected $_retry = 0;

    protected function _init()
    {
        $this->_ip = $this->getHelper("Utils")->getIP();
        $this->_cachekey .= "-" . $this->_ip;
    }

    protected function get()
    {
        if (!$this->_hold) {
            $res = $this->getHelper("Resource")->getMemcached()->get($this->_cachekey, null, Memcached::GET_EXTENDED);
            if ($res) {
                $this->_cas = $res['cas'];
                $this->_value = (array)$res['value'];
            } else {
                $this->_cas = null;
                $this->_value = array();
            }
            $this->_hold = true;
        }
        return $this->_value;
    }

    protected function set(array $val) :bool
    {
        //乐观锁设置
        if ($this->_cas) {
            $ok = $this->getHelper("Resource")->getMemcached()->cas($this->_cas, $this->_cachekey, $val, $this->_expiration);
        } else {
            $ok = $this->getHelper("Resource")->getMemcached()->add($this->_cachekey, $val, $this->_expiration);
        }

        if (!$ok) {
            $this->_hold = false;

            //重试多次后直接成功
            if ($this->_retry++ > 3) {
                $this->_retry = 0;
                return true;
            }
        }

        return $ok;
    }

    protected function incr(string $key) :int
    {
        do {
            $val = $this->get();
            if (!isset($val[$key])) {
                $val[$key] = 0;
            }
            $val[$key]++;
        } while(!$this->set($val));

        return $val[$key];
    }

    public function isLocked() :bool
    {
        $val= $this->get();
        return isset($val['isLocked']) ? $val['isLocked'] : false;
    }

    public function setLocked(string $key)
    {
        do {
            $val = $this->get();
            $val['isLocked'] = true;
            unset($val[$key]);
        } while(!$this->set($val));

        //记录锁定日志
        $msg = $this->_ip . " " . $key;
        $this->_ctx->getLogger('security')->info($msg);
    }

}
