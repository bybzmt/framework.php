<?php
namespace Bybzmt\Blog\Common;

use Memcached;

/**
 * 数据库表
 */
trait TableRowCache
{
    protected $_keyPrefix;
    protected $_hashPrefix;
    protected $_expiration = 1800;

    /**
     * 得到数据,缓存未命中时从数据库中加载
     */
    public function get(string $id)
    {
        $row = $this->getCache($id);
        if ($row === null) {
            $row = parent::get($id);
            $this->setCache($id, $row);
        }
        return $row;
    }

    /**
     * 批量得到数据,缓存未命中时从数据库中加载
     */
    public function gets(array $ids)
    {
        $out = $miss = array();

        foreach ($this->getCaches($ids) as $id=>$row) {
            if ($row) {
                $out[$id] = $row;
            } else if ($row===null) {
                $miss[] = $id;
            }
        }

        if ($miss) {
            $rows = parent::gets($miss);

            $new_caches = array();
            foreach ($miss as $id) {
                $row = isset($rows[$id]) ? $rows[$id] : false;

                $new_caches[$id] = $row;

                if ($row) {
                    $out[$id] = $row;
                }
            }

            $this->setCaches($new_caches);
        }

        return $out;
    }

    //添加一条记录(同时更新数据库和缓存)
    public function insert(array $row)
    {
        $id = parent::insert($row);
        if ($id) {
            //对分表做特殊处理
            if ($this instanceof TableSplit) {
                $key = rtrim($row[$this->_primary], ":") .":". $id;
            } else {
                $key = $id;
            }

            $row[$this->_primary] = $id;

            //字段数量一至时直接缓存，否则仅册除缓存
            if (count($row) == count($this->_columns)) {
                $this->setCache($key, $row);
            } else {
                $this->delCache($key);
            }
        }

        return $id;
    }

    //修改数据(同时更新数据库和缓存)
    public function update(string $id, array $row)
    {
        $ok = parent::update($id, $row);
        if ($ok) {
            $this->updateCache($id, $row);
        }
        return $ok;
    }

    //删除数据(同时更新数据库和缓存)
    public function delete(string $id)
    {
        $ok = parent::delete($id);
        if ($ok) {
            //缓存数据
            $this->setCache($id, false);
        }
        return $ok;
    }

    /**
     * 仅从缓存中取得数据
     */
    public function getCache(string $id)
    {
        return $this->unserialize($this->_ctx->getMemcached()->get($this->getKey($id)));
    }

    /**
     * 批量得到数据,仅从缓存中加载
     */
    public function getCaches(array $ids)
    {
        $keys = $out = [];

        foreach ($ids as $id) {
            $keys[$this->getKey($id)] = $id;
        }

        $rows = $this->_ctx->getMemcached()->getMulti(array_keys($keys), Memcached::GET_PRESERVE_ORDER);
        //Memcached连接失败时会返回false修补下数据
        if (!$rows) {
            $rows = array_combine(array_keys($keys), array_fill(0, count($keys), false));
        }

        foreach ($rows as $key => $row) {
            $out[$keys[$key]] = $this->unserialize($row);
        }

        return $out;
    }

    /**
     * 原子修改己缓存的数据,保证下次不取到脏数据
     *
     * @param $row k/v数组或回调函数function(array $row):array
     */
    public function updateCache(string $id, $row_or_fn)
    {
        $key = $this->getKey($id);
        $memcached = $this->_ctx->getMemcached();

        for ($i=0; $i<3; $i++) {
            $res = $memcached->get($key, null, Memcached::GET_EXTENDED);
            if (!$res) {
                //未命中缓存不需要处理
                if ($memcached->getResultCode() == Memcached::RES_NOTFOUND) {
                    return true;
                }

                //其它情况尝试删除记录
                break;
            }

            $cas = $res['cas'];

            $old = $this->unserialize($res['value']);
            if (!$old) {
                //非一般行缓存直接删除记录
                break;
            }

            if (is_array($row_or_fn)) {
                foreach ($row_or_fn as $k => $v) {
                    $old[$k] = $v;
                }
            } else {
                //回调函数
                $old = $row_or_fn($old);
            }

            $ok = $memcached->cas($cas, $key, $old);
            if ($ok) {
                return true;
            }

            //必须是数据己被修改而不是其它异常情况
            if ($memcached->getResultCode() != Memcached::RES_DATA_EXISTS) {
                break;
            }
        }

        //各种尝试失败后直接删除key确保不取到脏数据
        $memcached->delete($key);
        return true;
    }

    /**
     * 直接设置缓存
     */
    public function setCache(string $id, $row)
    {
        $key = $this->getKey($id);
        return $this->_ctx->getMemcached()->set($key, $this->serialize($row), $this->_expiration);
    }

    /**
     * 批量设置缓存
     */
    public function setCaches(array $rows)
    {
        if (!$rows) {
            return true;
        }

        $data = [];
        foreach ($rows as $id => $row) {
            $data[$this->getKey($id)] = $this->serialize($row);
        }

        return $this->_ctx->getMemcached()->setMulti($data, $this->_expiration);
    }

    /**
     * 删除缓存
     */
    public function delCache(string $id): bool
    {
        $key = $this->getKey($id);
        return $this->_ctx->getMemcached()->delete($key);
    }

    /**
     * 批量删除缓存
     */
    public function delCaches(array $ids): bool
    {
        $keys = [];
        foreach ($ids as $id) {
            $key[] = $this->getKey($id);
        }

        return $this->_ctx->getMemcached()->deleteMulti($key);
    }

    protected function getKey(string $id): string
    {
        if (!$this->_keyPrefix) {
            $this->_keyPrefix = static::class;
        }

        return $this->_keyPrefix .":". $id;
    }

    protected function serialize($data)
    {
        $str = serialize($data);
        //生成hash前缀
        return $this->hash($str) . $str;
    }

    protected function unserialize($data)
    {
        if (!is_string($data) || !$data) {
            return null;
        }

        $str = substr($data, 8);

        $hash = $this->hash($str);

        //验证数据是否损坏
        //实际使用中会发生表结构变动，缓存串key，缓存异常等情况
        //虽然一般这些损坏都是代码bug或代码改动造成的
        //理论上代码无bug且没有变动时不会出现损坏，但好的程序应该有
        //较好的容错性和健壮性，这里推荐坚持验证
        if (strncmp($hash, $data, 8) != 0) {
            return null;
        }

        return unserialize($str);
    }

    protected function hash(string $str): string
    {
        if (!$this->_hashPrefix) {
            $this->_hashPrefix = $this->_dbName.$this->_tableName.$this->_primary.implode($this->_columns);
        }
        return hash("crc32b", $this->_hashPrefix.$str);
    }
}
