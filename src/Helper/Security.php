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
        $this->_ip = $this->_ctx->request->server['remote_addr'];
        $this->_cachekey .= "-" . $this->_ip;
    }

    protected function get()
    {
        if (!$this->_hold) {
            $res = $this->_ctx->get("Resource")->getMemcached()->get($this->_cachekey, null, Memcached::GET_EXTENDED);
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
            $ok = $this->_ctx->get("Resource")->getMemcached()->cas($this->_cas, $this->_cachekey, $val, $this->_expiration);
        } else {
            $ok = $this->_ctx->get("Resource")->getMemcached()->add($this->_cachekey, $val, $this->_expiration);
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

    //展示验证码次数
    public function incr_showCaptcha()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 100) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //验证码错误次数
    public function incr_captchaError()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 20) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //登陆操作次数
    public function incr_doLogin()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 30) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //注册用户次数
    public function incr_doRegister()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 30) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //成功注册用户数量
    public function incr_registerSuccess()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 5) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //用户名或密码错误次数
    public function incr_UserOrPassError()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 10) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //发表评论次数
    public function incr_addComment()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 50) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //发表文章数量
    public function incr_addArticle()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 100) {
            $this->setLocked(__FUNCTION__);
        }
    }

    //新会话产生次数
    public function incr_newSession()
    {
        $num = $this->incr(__FUNCTION__);
        if ($num > 100) {
            $this->setLocked(__FUNCTION__);
        }
    }

}
