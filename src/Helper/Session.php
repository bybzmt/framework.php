<?php
namespace Bybzmt\Framework\Helper;

use Memcached;

/**
 * 安全
 */
class Session implements \ArrayAccess, \Iterator
{
    private $_prefix = "session_";
    private $_expiration = 60*60*2;

    private $_change;
    private $_init;

    private $_sid;
    private $_data = array();
    private $_ctx;
    private $_last;
    private $_now;

    public function __construct($context)
    {
        $this->_ctx = $context;
    }

    public function __destruct()
    {
        $this->save();
    }

    public function destroy()
    {
        $this->init();
        $this->_data = array();
        return $this->_ctx->get("Resource")->getMemcached()->delete($this->_prefix.$this->_sid);
    }

    private function init()
    {
        if ($this->_init) {
            return;
        }
        $this->_init = true;

        $skey = 'PHPSESSID';
        $this->_sid = isset($this->_ctx->request->cookie[$skey]) ? $this->_ctx->request->cookie[$skey] : null;
        if (!$this->_sid) {
            $this->_sid = $this->create_sid();
            $this->_ctx->response->cookie($skey, $this->_sid);
        }

        $data = $this->read();
        if (!is_array($data)) {
            $data = array();
        }

        $this->_last = isset($data['last']) ? $data['last'] : 0;
        $this->_data = isset($data['data']) ? $data['data'] : array();

        //session哪怕未改变也需要周期性刷新
        $this->_now = $this->_ctx->request->server['request_time'];
        $this->_change = ($this->_now-$this->_last > 60);
    }

    private function read()
    {
        $res = $this->_ctx->get("Resource")->getMemcached()->get($this->_prefix.$this->_sid, null, Memcached::GET_EXTENDED);
        if ($res) {
            return $res['value'];
        } else {
            //判断确实未找到,而非memcache服务器出问题了
            if ($this->_ctx->get("Resource")->getMemcached()->getResultCode() == Memcached::RES_NOTFOUND) {
                $this->_ctx->get("Helper.Security")->incr_newSession();
            }
            return '';
        }
    }

    public function save()
    {
        if ($this->_change) {
            $this->_change = false;

            $data = array('last'=>$this->_now, 'data' => $this->_data);

            $this->_ctx->get("Resource")->getMemcached()->set($this->_prefix.$this->_sid, $data, $this->_expiration);
        }
    }

    public function get($key)
    {
        return $this->offsetGet($key);
    }

    private function create_sid()
    {
        return sha1(microtime(true).mt_rand());
    }

    public function offsetSet($offset, $value)
    {
        $this->init();
        $this->_change = true;
        $this->_data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->init();
        $this->_change = true;
        unset($this->_data[$offset]);
    }

    public function offsetExists($offset)
    {
        $this->init();
        return isset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->init();
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    public function current()
    {
        $this->init();
        return current($this->_data);
    }

    public function key()
    {
        $this->init();
        return key($this->_data);
    }

    public function next()
    {
        $this->init();
        next($this->_data);
    }

    public function rewind()
    {
        $this->init();
        reset($this->_data);
    }

    public function valid()
    {
        $this->init();
        return key($this->_data) !== null;
    }
}
