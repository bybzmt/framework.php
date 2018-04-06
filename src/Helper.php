<?php
namespace Bybzmt\Framework;

/**
 * 助手类公共基类
 */
abstract class Helper extends Component
{
    public function __construct(Context $context)
    {
        parent::__construct($context);

        $this->_init();
    }

    protected function _init()
    {
    }
}
