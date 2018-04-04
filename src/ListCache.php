<?php
namespace Bybzmt\Framework;

abstract class ListCache extends Cache
{
    //缓存的最大id数量
    protected $size = 3000;

    abstract protected function findRows(array $ids):array;

    public function getlist(int $offset, int $length): array
    {
        $ids = array_slice($this->get(), $offset, $length);
        return $this->findRows($ids);
    }

    public function count()
    {
        return count($this->get());
    }

    //用于一个值插入到列表的头部(最左边)
    public function itemLPush($id) : bool
    {
        $ids = $this->get();
        $ids = array_diff($ids, [$id]);

        array_unshift($ids, $id);

        while (count($ids) > $this->size) {
            array_pop($ids);
        }

        return $this->setAllIds($ids);
    }

    //用于一个值插入到列表的尾部(最右边)
    public function itemRPush($id) : bool
    {
        $ids = $this->get();
        $ids = array_diff($ids, [$id]);

        array_push($ids, $id);

        while (count($ids) > $this->size) {
            array_shift($ids);
        }

        return $this->setAllIds($ids);
    }

    public function delItem($id) : bool
    {
        $ids = array_diff($this->get(), [$id]);
        return $this->setAllIds($ids);
    }


}
