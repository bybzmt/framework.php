<?php
namespace Bybzmt\Framework;

/**
 * 环境上下对像
 */
class Context
{
    //请求对像
    public $request;

    //响应对像
    public $response;

    public $session = array();

    //db连接
    protected $dbConns;

    //memcached连接
    protected $memcachedConns;

    //redis连接
    protected $redisConns;

    //日志
    protected $loggers;

    //缓存对像
    protected $caches;

    //数据表对像
    protected $tables;

    //服务对像
    protected $services;

    //助手对像
    protected $helpers;

    //标记的批量加载
    public $lazyRow;

    ####################
    ## 初始化基础对像 ##

    /**
     * 初始化数据表对象
     */
    public function initHelerps(string $name)
    {
        $class = __NAMESPACE__ ."\\Helper\\". $name;
        return new $class($this);
    }

    /**
     * 初始化数据表对象
     */
    public function initService(string $name)
    {
        $class = __NAMESPACE__ ."\\Service\\". $name;
        return new $class($this);
    }

    /**
     * 初始化数据表对象
     */
    public function initTable(string $name)
    {
        $class = __NAMESPACE__ ."\\Table\\". $name;
        return new $class($this);
    }

    /**
     * 初始化缓存对像
     */
    public function initCache(string $name, string $id='', ...$args)
    {
        $class = __NAMESPACE__ ."\\Cache\\". $name;
        return new $class($this, $id, ...$args);
    }

    /**
     * 初始化一个数据行对像
     */
    public function initRow(string $name, array $row)
    {
        $class = __NAMESPACE__ . "\\Row\\" . $name;
        return new $class($this, $row);
    }

    ##################
    ## 加载基本服务 ##

    /**
     * 得到助手对象(与业务无关的工具)
     */
    public function getHelper(string $name)
    {
        if (!isset($this->helpers[$name])) {
            $this->helpers[$name] = $this->initHelerps($name);
        }
        return $this->helpers[$name];
    }

    /**
     * 得到服务对象(业务)
     */
    public function getService(string $name)
    {
        if (!isset($this->services[$name])) {
            $this->services[$name] = $this->initService($name);
        }
        return $this->services[$name];
    }

    /**
     * 得到数据表对象
     */
    public function getTable(string $name)
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = $this->initTable($name);
        }
        return $this->tables[$name];
    }

    /**
     * 得到缓存对像
     */
    public function getCache(string $name, string $id='', ...$args)
    {
        $cache_id = $name . $id;

        if (!isset($this->caches[$cache_id])) {
            $this->caches[$cache_id] = $this->initCache($name, $id, ...$args);
        }

        return $this->caches[$cache_id];
    }

    ##############
    ## 数据加截 ##

    /**
     * 直接加载一个数据行对像
     */
    public function getRow(string $name, string $id)
    {
        $row = $this->getTable($name)->get($id);

        return $row ? $this->initRow($name, $row) : false;
    }

    public function getRows(string $name, array $ids)
    {
        $rows = $this->getTable($name)->gets($ids);

        $obj = array();
        foreach ($rows as $row) {
            $obj[] = $this->initRow($name, $row);
        }
        return $obj;
    }

    /**
     * 惰性加载一个数据行对像
     */
    public function getLazyRow(string $name, string $id)
    {
        return new LazyRow($this, $name, $id);
    }

    public function getLazyRows(string $name, array $ids)
    {
        $obj = array();
        foreach ($ids as $id) {
            $obj[] = new LazyRow($this, $name, $id);
        }
        return $obj;
    }

    ######################
    ## 连接各种外部资源 ##

    public function getMemcached($name='default')
    {
        if (!isset($this->memcachedConns[$name])) {
            $this->memcachedConns[$name] = Resource::getMemcached($name);
        }

        return $this->memcachedConns[$name];
    }

	public function getRedis($name='default')
	{
		if (!isset($this->redisConns[$name])) {
			$this->redisConns[$name] = Resource::getRedis($name);
		}

		return $this->redisConns;
	}

    public function getDb($name='default')
    {
        if (!isset($this->dbConns[$name])) {
            $this->dbConns[$name] = Resource::getDb($name);
        }

        return $this->dbConns[$name];
    }

    public function getLogger($name='default')
    {
		if (!isset($this->loggers[$name])) {
			$this->loggers[$name] = Resource::getLogger($name);
		}

		return $this->loggers[$name];
    }

}
