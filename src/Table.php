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

    /**
     * 按主键查找一行数据
     */
    public function get(string $id)
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

        list($sql, $params) = $this->_ctx->getHelper("SQLBuilder")->select($this->_columns, $this->_tableName, [$this->_primary=>$ids]);

        $rows = $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, null, $this->_primary);
    }

    public function insert(array $row)
    {
        if (!$row) {
            return false;
        }

        list($sql, $vals) = $this->_ctx->getHelper("SQLBuilder")->insert($this->_tableName, $row);

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

    public function update(string $id, array $row)
    {
        if (!$row) {
            return false;
        }

        list($sql, $vals) = $this->_ctx->getHelper("SQLBuilder")->update($this->_tableName, $row, [$this->_primary=>$id]);

        return $this->exec($sql, $vals);
    }

    public function delete(string $id)
    {
		$sql = "DELETE FROM `{$this->_tableName}` WHERE `{$this->_primary}` = ? LIMIT 1";

        return $this->exec($sql, [$id]);
    }

    protected function getDB(bool $isMaster=false)
    {
        return $this->_ctx->getDb($this->_dbName . ($isMaster?'_master':'_slave'));
    }

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
