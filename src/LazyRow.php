<?php
namespace Bybzmt\Blog\Common;

/**
 * 行数据惰性加载器
 */
class LazyRow extends Component
{
    protected $name;
    protected $id;
    protected $initd;
    protected $row;

    public function __construct(Context $context, string $name, string $id)
    {
        parent::__construct($context);

        $this->name = $name;
        $this->id = $id;

        $context->lazyRow[$name][$id][] = $this;
    }

    protected function _do_set_row($row)
    {
        $this->initd = true;
        $this->row = $row;
    }

    /**
     * 属性访问回调钩子
     */
    public function __get($key)
    {
        if (!$this->initd) {
            $this->init();
            $this->initd = true;
        }

        return $this->row ? $this->row->$key : null;
    }

    /**
     * 属性判断回调钩子
     */
    public function  __isset($key)
    {
        if (!$this->initd) {
            $this->init();
            $this->initd = true;
        }

        return $this->row ? isset($this->row->$key) : null;
    }

    /**
     * 方法访问回调钩子
     */
    public function __call($name, $params)
    {
        if (!$this->initd) {
            $this->init();
            $this->initd = true;
        }

        if ($this->row) {
            if (method_exists($this->row, $name)) {
                return $this->row->$name(...$params);
            }
        }

        throw new Exception("Row {$this->name} not exists method {$name}");
    }

    protected function init()
    {
        $this->rowLoad();
    }

    /**
     * 批量加载
     */
    protected function rowLoad()
    {
        $ids = array_keys($this->_ctx->lazyRow[$this->name]);

        $rows = $this->_ctx->getTable($this->name)->gets($ids);

        foreach ($this->_ctx->lazyRow[$this->name] as $id => $lazyRows) {
            if (isset($rows[$id])) {
                $obj = $this->_ctx->initRow($this->name, $rows[$id]);

                foreach ($lazyRows as $lazyRow) {
                    $lazyRow->_do_set_row($obj);
                }
            } else {
                foreach ($lazyRows as $lazyRow) {
                    $lazyRow->_do_set_row(false);
                }
            }
        }

        unset($this->_ctx->lazyRow[$this->name]);
    }

}
