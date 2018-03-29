<?php
namespace Bybzmt\Framework\Helper;

use Bybzmt\Framework\Helper;

/**
 * 常用SQL快速拼接器
 */
class SQLBuilder extends Helper
{
    public function insert(string $table, array $row)
    {
		$keys = implode('`, `', array_keys($row));
        $holds = implode(',', array_fill(0, count($row), '?'));

		$sql = "INSERT INTO `{$table}` (`{$keys}`) VALUES({$holds})";

        return [$sql, array_values($row)];
    }

    public function inserts(string $table, array $rows)
    {
		$holds = array();

		$feilds = array_keys(reset($rows));

        $hold = '('.implode(',', array_fill(0, count($feilds), '?')).')';
		$holds = implode(",\n", array_fill(0, count($rows), $hold));
		$feilds = implode("`,`", $feilds);

        $vals = [];
        foreach ($rows as $row) {
            $vals = array_merge($vals, array_values($row));
        }

		$sql = "INSERT INTO `{$table}` (`{$feilds}`)\n VALUES {$holds}";

        return [$sql, $vals];
    }

    public function select(array $columns, string $table, array $where, int $offset=0, int $length=0)
    {
        $_columns = implode("`,`", $columns);

        $tmp = array();

        $sql = "SELECT `$_columns` FROM `{$table}` WHERE " . self::where($where, $tmp);

        if ($length > 0) {
            $sql .= " LIMIT $offset, $length";
        }

        return [$sql, $tmp];
    }

    public function update(string $table, array $feilds, array $where)
    {
		$set = array();
        $vals = array();

		foreach ($feilds as $key => $val) {
			$set[] = "`{$key}` = ?";
            $vals[] = $val;
		}

		$set = implode(', ', $set);

		$sql = "UPDATE `{$table}` SET {$set} WHERE ".self::where($where, $vals)." LIMIT 1";

        return [$sql, $vals];
    }

	public function delete($table, array $where, $limit=0)
	{
        $tmp = array();

		$sql = "DELETE FROM {$table} WHERE " . self::where($where, $tmp);

		if ($limit) {
			$sql .= " LIMIT ".(int)$limit;
		}

        return [$sql, $tmp];
	}

	public function where(array $where, array &$tmp)
	{
		$_where = array();

		foreach ($where as $key => $val) {
			if (is_array($val)) {
				$_where[] = "`{$key}` IN (" . implode(",", array_fill(0, count($val), '?')) . ')';
                $tmp = array_merge($tmp, $val);
			}
			else {
				$_where[] = "`{$key}` = ?";
                $tmp[] = $val;
			}
		}

		return implode(' AND ', $_where);
	}
}
