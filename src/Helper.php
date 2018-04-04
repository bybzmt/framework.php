<?php
namespace Bybzmt\Framework;

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
