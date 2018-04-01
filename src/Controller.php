<?php
namespace Bybzmt\Framework;

use Throwable;

abstract class Controller extends Component
{
    public function getQuery($key)
    {
        return isset($this->_ctx->request->get[$key]) ? $this->_ctx->request->get[$key] : null;
    }

    public function getPost($key)
    {
        return isset($this->_ctx->request->post[$key]) ? $this->_ctx->request->post[$key] : null;
    }

    public function getCookie($key)
    {
        return isset($this->_ctx->request->cookie[$key]) ? $this->_ctx->request->cookie[$key] : null;
    }

    public function execute()
    {
        try {
            $this->init();

            if ($this->valid() && $this->exec()) {
                $this->show();
            } else {
                $this->fail();
            }
        } catch(Throwable $e) {
            $this->onException($e);
        }
    }

    /**
     * 初始化 接好各种输入并进行适当的格式化, 这部不能报任何错误
     */
    abstract public function init();

    /**
     * 对接收到的各种数据进行验证 成功返回true
     */
    abstract public function valid();

    /**
     * 进行数据操作 成功返回true
     */
    abstract public function exec();

    /**
     * 展示验证或操作失败的结果
     */
    abstract public function fail();

    /**
     * 异常处理
     */
    abstract public function onException($e);

    /**
     * 展示正常的输出
     */
    abstract public function show();
}
