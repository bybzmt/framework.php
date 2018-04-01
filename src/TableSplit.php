<?php
namespace Bybzmt\Framework;

use PDO;
use PDOStatement;
use Flexihash\Flexihash;

/**
 * 数据库表
 */
class TableSplit extends Table
{
    protected $_tablePrefix;
    protected $_tableNum;

    protected function _setTable(string $split_id)
    {
        //一至性hash
        static $hash;
        if (!$hash) {
            $hash = new Flexihash();
            $hash->addTargets(range(0, $this->_tableNum-1));
        }

        $this->_tableName = $this->_tablePrefix . $hash->lookup($split_id);
    }

    public function get(string $key)
    {
        list($split_id, $id) = explode(":", $key.":");

        $this->_setTable($split_id);

        return parent::get($id);
    }

    public function gets(array $keys)
    {
        if (!$keys) {
            return [];
        }

        //从key中拆分出分表依据和id
        $table_ids = array();
        $split_ids = array();
        foreach ($keys as $key) {
            list($split_id, $id) = explode(":", $key.":");

            $this->_setTable($split_id);

            $split_ids[$id] = $key;

            $table_ids[$this->_tableName][] = $id;
        }

        $sqls = array();
        $params = array();
        //创建sql
        foreach ($table_ids as $tableName => $ids) {
            list($sql, $tmp) = $this->getHelper("SQLBuilder")->select($this->_columns, $tableName, [$this->_primary=>$ids]);

            $sqls[] = $sql;
            $params = array_merge($params, $tmp);
        }
        //连接多条sql到一起
        $sql = implode(" UNION ", $sqls);

        $rows = $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);

        $out = array();
        foreach ($rows as $row) {
            $key = $split_ids[$row[$this->_primary]];
            $out[$key] = $row;
        }
        return $out;
    }

    public function insert(array $row)
    {
        if (!isset($row[$this->_primary])) {
            throw new Exception("TableSplit primary key is must");
        }

        list($split_id, $id) = explode(":", $row[$this->_primary].":");

        $this->_setTable($split_id);

        if ($id) {
            $row[$this->_primary] = $id;
        } else {
            $row[$this->_primary] = $this->_autoIncrement();
        }

        return parent::insert($row);
    }

    public function update(string $key, array $row)
    {
        list($split_id, $id) = explode(":", $key.":");

        $this->_setTable($split_id);

        return parent::update($id, $row);
    }

    public function delete(string $key)
    {
        list($split_id, $id) = explode(":", $key.":");

        $this->_setTable($split_id);

        return parent::delete($id);
    }

    protected function _autoIncrement(int $num=1)
    {
        $tableName = $this->_tablePrefix . "id";

        //手动处理分表时的自增id
        $sql = "insert into $tableName (id) values(null)";
        if ($num > 1) {
            $sql .= str_repeat(",(null)", $num-1);
        }

        $db = $this->getDB(true);

        $ok = $db->exec($sql);
        if (!$ok) {
            throw new Exception("AUTO_INCREMENT Error");
        }

        $id = $db->lastInsertId();
        if (!$id) {
            throw new Exception("AUTO_INCREMENT Error");
        }

        //定期清理无用id
        if ($id % 1000 == 0) {
            $sql = "delete from $tableName where id < ?";
            $db->exec($sql, [$id]);
        }

        return $id;
    }

}
