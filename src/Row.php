<?php
namespace Bybzmt\Blog\Common;

use IteratorAggregate;
use ArrayAccess;

abstract class Row extends Component implements IteratorAggregate, ArrayAccess
{
    protected $_row;

    public function __construct(Context $context, array $row)
    {
        parent::__construct($context);

        $this->_row = $row;

        $this->init();
    }

    protected function init()
    {
    }

    public function __isset($key)
    {
        return isset($this->_row[$key]);
    }

    public function __get($key)
    {
        return $this->_row[$key];
    }

    public function __set($key, $val)
    {
        $this->_row[$key] = $val;
    }

    public function __unset($key)
    {
        unset($this->_row[$key]);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->_row);
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_row[] = $value;
        } else {
            $this->_row[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->_row[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->_row[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_row[$offset];
    }
}
