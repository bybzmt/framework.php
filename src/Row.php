<?php
namespace Bybzmt\Framework;

/**
 * 数据行（领域模型）公共基类
 */
abstract class Row extends Component
{
    public function __construct(Context $context, array $row)
    {
        parent::__construct($context);

        foreach ($row as $key => $val) {
            $this->$key = $val;
        }

        $this->_init();
    }

    protected function _init()
    {
    }

}
