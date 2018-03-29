<?php
namespace Bybzmt\Blog\Common;

use PDO;
use Memcached;
use Redis;
use Bybzmt\DB\Monitor;
use Bybzmt\Logger\Factory;
use bybzmt\Locker\SocketLock;
use bybzmt\Locker\FileLock;

class Resource
{
    public static function getDb($name='default')
    {
        $cfgs = Config::get("db.{$name}");

        list($dsn, $user, $pass) = $cfgs[mt_rand(0, count($cfgs)-1)];

        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $db = new PDO($dsn, $user, $pass, $opts);

        $logger = self::getLogger('sql');

        $monitor = new Monitor($db, function($time, $sql, $params=[]) use($logger) {
            $msg = sprintf("time:%0.6f sql:%s", $time, $sql);
            $logger->info($msg, $params);
        });

        return $monitor;
    }

    public static function getMemcached($name='default')
    {
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

        return $client;
    }

	public static function getRedis($name='default')
	{
        $config = Config::get("redis.$name");

        $md = new Redis();
        $md->connect($config["host"], $config["port"], $config["timeout"]);
        if (!empty($config['password'])) {
            $md->auth($config['password']);
        }
        //$md->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
        //$md->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        //$md->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_IGBINARY);
        return $md;
	}

    public static function getLogger($name='default')
    {
        $cfgs = Config::get("log.$name");
        return Factory::getLogger($cfgs);
    }

	public static function getLocker($key)
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
}
