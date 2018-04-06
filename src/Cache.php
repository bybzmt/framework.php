<?php
namespace Bybzmt\Framework;

/**
 * 缓存对像公共基类
 */
abstract class Cache extends Component
{
    //使用哪个memcached
    protected $memcachedName = 'default';

    //缓存过期时间
    protected $expiration = 1800;

    //缓存key
    protected $key;

    public function __construct(Context $ctx, ...$args)
    {
        parent::__construct($ctx);

        $this->key = strtr(static::class, '\\', '.');

        if (method_exists($this, '_init')) {
            $this->_init(...$args);
        }
    }

    abstract protected function load();

    public function get()
    {
        $data = $this->unserialize($this->getMemcached()->get($this->key));
        if ($data === null) {
            $data = $this->load();
            $this->set($data);
        }
        return $data;
    }

    public function set($data)
    {
        return $this->getMemcached()->set($this->key, $this->serialize($data), $this->expiration);
    }

    public function del()
    {
        return $this->getMemcached()->delete($this->key);
    }

    protected function getMemcached()
    {
        return $this->getHelper("Resource")->getMemcached($this->memcachedName);
    }

    protected function hash(string $str): string
    {
        return hash("crc32b", $this->key.$str);
    }

    protected function serialize($data)
    {
        $str = serialize($data);
        //生成hash前缀
        return $this->hash($str) . $str;
    }

    protected function unserialize($data)
    {
        if (!$data) {
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
}
