<?php
namespace Bybzmt\Framework;

abstract class Row extends Component
{
    public function __construct(Context $context, array $row)
    {
        parent::__construct($context);

        foreach ($row as $key => $val) {
            $this->$key = $val;
        }

        $this->init();
    }

    protected function init()
    {
    }

}
