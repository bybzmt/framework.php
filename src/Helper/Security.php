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

    public function isLocked() :bool
    {
        $val= $this->get();
        return isset($val['isLocked']) ? $val['isLocked'] : false;
    }

    protected function cache()
    {
        return $this->getHelper("Resource")->getMemcached();
    }

    protected function loginfo($msg)
    {
        $this->getHelper("Resource")->getLogger('security')->info($msg);
    }

    protected function get()
    {
        if (!$this->_hold) {
            $res = $this->cache()->get($this->_cachekey, null, Memcached::GET_EXTENDED);
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
            $ok = $this->cache()->cas($this->_cas, $this->_cachekey, $val, $this->_expiration);
        } else {
            $ok = $this->cache()->add($this->_cachekey, $val, $this->_expiration);
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

    protected function _incr(string $key) :int
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

    protected function _setLocked(string $key)
    {
        do {
            $val = $this->get();
            $val['isLocked'] = true;
            unset($val[$key]);
        } while(!$this->set($val));

        //记录锁定日志
        $this->loginfo($this->_ip . " " . $key);
    }

    //检查某项测试是否超过最大数值
    public function check(string $key, int $max)
    {
        $num = $this->_incr($key);
        if ($num > $max) {
            $this->_setLocked($key);
        }
    }

    //新会话产生次数
    public function incr_newSession()
    {
        $this->check(__FUNCTION__, 100);
    }
}
