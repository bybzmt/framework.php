<?php
namespace Bybzmt\Framework;

/**
 * 行数据惰性加载器
 * 实例化时并不访问数据库，只是记录下来，当来访问属性时再试图进行批量加载
 */
class LazyRow extends Component
{
    protected $name;
    protected $id;
    protected $initd;
    protected $row;

    static protected $lazyRow = array();

    public function __construct(Context $context, string $name, string $id)
    {
        parent::__construct($context);

        $this->name = $name;
        $this->id = $id;

        self::$lazyRow[$name][$id][] = $this;
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
        $ids = array_keys(self::$lazyRow[$this->name]);

        $rows = $this->getTable($this->name)->gets($ids);

        foreach (self::$lazyRow[$this->name] as $id => $objs) {
            $row = isset($rows[$id]) ? $this->initRow($this->name, $rows[$id]) : false;

            foreach ($objs as $obj) {
                $obj->_do_set_row($row);
            }
        }

        unset(self::$lazyRow[$this->name]);
    }

}
