<?php
namespace Bybzmt\Framework;

use PDO;
use PDOStatement;

/**
 * 数据库表
 */
abstract class Table extends Component
{
    //数据库名
    protected $_dbName;
    //表名
    protected $_tableName;
    //主键
    protected $_primary;
    //表字段
    protected $_columns;

    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->_init();
    }

    protected function _init()
    {
    }

    /**
     * 按主键查找一行数据
     */
    public function get($id)
    {
        $sql = "SELECT `".implode("`,`", $this->_columns)."`
            FROM `{$this->_tableName}` WHERE `{$this->_primary}` = ?";

        return $this->query($sql, [$id])->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 按主键查找一批数据
     */
    public function gets(array $ids)
    {
        if (!$ids) {
            return [];
        }

        list($sql, $params) = $this->getHelper("SQLBuilder")->select($this->_columns, $this->_tableName, [$this->_primary=>$ids]);

        $rows = $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, null, $this->_primary);
    }

    /**
     * 插入一行数据
     */
    public function insert(array $row)
    {
        if (!$row) {
            return false;
        }

        list($sql, $vals) = $this->getHelper("SQLBuilder")->insert($this->_tableName, $row);

        $affected = $this->exec($sql, $vals);
        if ($affected) {
            if (!isset($row[$this->_primary])) {
                return $this->getDB(true)->lastInsertId();
            } else {
                return $row[$this->_primary];
            }
        }
        return $affected;
    }

    /**
     * 按主键修改一行数据
     */
    public function update($id, array $row)
    {
        if (!$row) {
            return false;
        }

        list($sql, $vals) = $this->getHelper("SQLBuilder")->update($this->_tableName, $row, [$this->_primary=>$id]);

        return $this->exec($sql, $vals);
    }

    /**
     * 删除一行数据
     */
    public function delete($id)
    {
		$sql = "DELETE FROM `{$this->_tableName}` WHERE `{$this->_primary}` = ? LIMIT 1";

        return $this->exec($sql, [$id]);
    }

    /**
     * 得到数据库连接
     */
    protected function getDB(bool $isMaster=false)
    {
        return $this->getHelper("Resource")->getDb($this->_dbName . ($isMaster?'_master':'_slave'));
    }

    /**
     * 执行SQL查询(默认从库)
     */
    protected function query(string $sql, array $params=[], bool $isMaster=false):PDOStatement
    {
        if ($params) {
            $stmt = $this->getDB($isMaster)->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $ok = $stmt->execute($params);
            if (!$ok) {
                return false;
            }

            return $stmt;
        } else {
            return $this->getDB($isMaster)->query($sql);
        }
    }

    /**
     * 执行SQL修改(默认主库)
     */
    protected function exec(string $sql, array $params=[])
    {
        if ($params) {
            $stmt = $this->getDB(true)->prepare($sql);
            if (!$stmt) {
                return false;
            }

            $ok = $stmt->execute($params);
            if (!$ok) {
                return false;
            }

            return $stmt->rowCount();
        } else {
            return $this->getDB(true)->exec($sql);
        }
    }

}
