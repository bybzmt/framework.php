<?php
namespace Bybzmt\Framework;

/**
 * 根组件
 *
 * 只是一个象征性的根组件
 */
abstract class Component
{
    use ComponentTrait;

    protected $_ctx;

    public function __construct(Context $context)
    {
        $this->_ctx = $context;
    }
}
