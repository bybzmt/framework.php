<?php
namespace Bybzmt\Blog\Common\Cache;

use Bybzmt\Blog\Common\Cache as PCache;
use Bybzmt\Blog\Common\Context;

abstract class ListCache extends PCache
{
    //缓存过期时间
    protected $expiration = 1800;

    //缓存的最大id数量
    protected $size = 10000;

    //列表缓存id
    protected $list_id;

    public function __construct(Context $context, string $list_id='')
    {
        $this->_ctx = $context;
        $this->list_id = $list_id;
        $this->key = str_replace('\\', '.', static::class) .'.'. $list_id;
        $this->_hashPrefix = $this->key;
    }

    abstract protected function getRows(array $ids):array;

    abstract protected function loadData(int $limit):array;

    public function gets(int $offset, int $length): array
    {
        $ids = array_slice($this->getAllIds(), $offset, $length);
        return $this->getRows($ids);
    }

    public function count()
    {
        return count($this->getAllIds());
    }

    //用于一个值插入到列表的头部(最左边)
    public function itemLPush(string $id) : bool
    {
        $ids = $this->getAllIds();
        $ids = array_diff($ids, [$id]);

        array_unshift($ids, $id);

        while (count($ids) > $this->size) {
            array_pop($ids);
        }

        return $this->setAllIds($ids);
    }

    //用于一个值插入到列表的尾部(最右边)
    public function itemRPush(string $id) : bool
    {
        $ids = $this->getAllIds();
        $ids = array_diff($ids, [$id]);

        array_push($ids, $id);

        while (count($ids) > $this->size) {
            array_shift($ids);
        }

        return $this->setAllIds($ids);
    }

    public function delItem(string $id) : bool
    {
        $ids = array_diff($this->getAllIds(), [$id]);
        return $this->setAllIds($ids);
    }

    public function getAllIds()
    {
        $ids = $this->unserialize($this->_ctx->getMemcached()->get($this->key));
        if ($ids === null) {
            $ids = $this->loadData($this->size);
            $this->setAllIds($ids);
        }
        return $ids;
    }

    public function setAllIds(array $ids)
    {
        return $this->_ctx->getMemcached()->set($this->key, $this->serialize($ids), $this->expiration);
    }

    public function del()
    {
        return $this->_ctx->getMemcached()->delete($this->key);
    }


}
