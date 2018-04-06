<?php
namespace Bybzmt\Framework;

/**
 * Service公共基类
 */
abstract class Service extends Component
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
