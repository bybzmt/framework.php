<?php
namespace Bybzmt\Blog\Common;

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
