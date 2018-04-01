<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Component;
use Bybzmt\Framework\Config;
use PDO;
use Memcached;
use Redis;
use Bybzmt\DB\Monitor;
use Bybzmt\Logger\Factory;
use Bybzmt\Locker\SocketLock;
use Bybzmt\Locker\FileLock;
use Bybzmt\HttpStorage\SimpleHttpStorage;

//连接各种外部资源
class Resource extends Component
{
    //db连接
    protected $dbConns;

    //memcached连接
    protected $memcachedConns;

    //redis连接
    protected $redisConns;

    //日志
    protected $loggers;

    protected $fileManagers;

    public function getMemcached($name='default')
    {
        if (!isset($this->memcachedConns[$name])) {
            $config = Config::get("memcached.$name");

            $client = new Memcached($config['persistent_id']);

            $client->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            $client->setOption(Memcached::OPT_TCP_NODELAY, true);
            $client->setOption(Memcached::OPT_NO_BLOCK, true);
            $client->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

            $now = $client->getServerList();
            if (!$now) {
                $client->addServers($config['servers']);
            }

            $this->memcachedConns[$name] = $client;
        }

        return $this->memcachedConns[$name];
    }

	public function getRedis($name='default')
	{
		if (!isset($this->redisConns[$name])) {
            $config = Config::get("redis.$name");

            $md = new Redis();
            $md->connect($config["host"], $config["port"], $config["timeout"]);
            if (!empty($config['password'])) {
                $md->auth($config['password']);
            }
            //$md->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            //$md->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            //$md->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
            $this->redisConns[$name] = $md;
		}

		return $this->redisConns;
	}

    public function getDb($name='default')
    {
        if (!isset($this->dbConns[$name])) {
            $cfgs = Config::get("db.{$name}");

            list($dsn, $user, $pass) = $cfgs[mt_rand(0, count($cfgs)-1)];

            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $db = new PDO($dsn, $user, $pass, $opts);

            $logger = $this->getLogger('sql');

            $monitor = new Monitor($db, function($time, $sql, $params=[]) use($logger) {
                $msg = sprintf("time:%0.6f sql:%s", $time, $sql);
                $logger->info($msg, $params);
            });

            $this->dbConns[$name] = $monitor;
        }

        return $this->dbConns[$name];
    }

    public function getLogger($name='default')
    {
		if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = Factory::getLogger(Config::get("log.$name"));
		}

		return $this->loggers[$name];
    }

	public function getLocker($key)
	{
        $config = Config::get("locker");

        switch($config['type']) {
        case 'socket':
            return new SocketLock($key, $config["host"], $config["port"], $config["timeout"]);
        case 'file':
            return new FileLock($key);
        default:
            throw new Exception("未定义的锁类型: {$config['type']}");
        }
	}

	/**
	 * 得到文件管理服务
	 * @param string 文件管理服务器名
	 * @return bybzmt\HttpStorage\SimpleHttpStorage
	 */
	public function getFileManager($name='default')
	{
		if (!isset($this->fileManagers[$name])) {
			$config = Config::get("fileManager.$name");

			$storage = new SimpleHttpStorage(
				$config['host'], $config['port'], $config['timeout']
			);

			$this->fileManagers[$name] = $storage;
		}

		return $this->fileManagers[$name];
	}
}
