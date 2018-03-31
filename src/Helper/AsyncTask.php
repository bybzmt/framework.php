<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Helper;

/**
 * 文件存储服务
 */
class AsyncTask extends Helper
{
	/**
	 * 异步任务(立刻执行)
	 *
	 * @param string $action 动作路径，对应backend模块下的控制器
	 * @param array $parms 参数，作为GET参数传给控制器
	 */
	static public function AsyncTask($action, array $params=array())
	{
		$default = self::getParam('task_default');
		if ($default) {
			$params += $default;
		}

		$redis = self::getRedis();

		$redis->RPUSH(Library\RedisKey::ASYNC_TASK_QUEUE_JSON, json_encode(array(
			'action' => $action,
			'params' => http_build_query($params)
		)));
	}

	/**
	 * 定时任务(精度为分)
	 *
	 * @param int $time 定时的时间点
	 * @param string $action 动作路径，对应backend模块下的控制器
	 * @param array $parms 参数，作为GET参数传给控制器
	 */
	static public function TimerTask($time, $action, array $params=array())
	{
		$default = self::getParam('task_default');
		if ($default) {
			$params += $default;
		}

		$db = $this->_ctx->get("Resource")->getDb("blog_master");
		$db->insert('timer_task', array(
			'run_time' => date('Y-m-d H:i:s', $time),
			'action' => $action,
			'params' => serialize($params),
		));
	}

	/**
	 * 设置参数(跨层参数传递用)
	 */
	static public function setParam($key, $param)
	{
		self::$_params[$key] = $param;
	}

	/**
	 * 取得参数(跨层参数传递用)
	 */
	static public function getParam($key)
	{
		return isset(self::$_params[$key]) ? self::$_params[$key] : null;
	}



}
