<?php

class Db_Tools
{
	/**
	 * @var Db
	 */
	private $db = null;
	
	public function __construct(Db $db)
	{
		$this->db = $db;
	}
	
	public function getTables()
	{
		return $this->db->fetchCol('SHOW TABLES');
	}

	public function getStructure($tables = null)
	{
		if ($tables == null)
		{
			$tables = $this->getTables();
		}
		$current = array();
		foreach ($tables as $t)
		{
			$current[$t] = array();
			//var_dump('SHOW CREATE TABLE ' . $t);
			$create = $this->db->fetchRow('SHOW CREATE TABLE `' . $t . '`');
			$create = $create['Create Table'];
			$create = substr($create, strpos($create, '(') + 1, strrpos($create, ')') - strpos($create, '(') - 1);
			$create = explode(',' . "\n", $create);
			foreach ($create as $row)
			{
				if (strpos($row, 'KEY') !== false)
				{
					if (strpos($row, 'PRIMARY') !== false)
					{
						$current[$t]['index_primary'] = trim($row);
					}
					else
					{
						$start = strpos($row, '`') + 1;
						$current[$t][substr($row, $start, strpos($row, '`', $start) - $start)] = trim($row);
					}
				}
				else
				{
					$current[$t][substr($row, strpos($row, '`') + 1, strrpos($row, '`') - strpos($row, '`') - 1)] =
					trim(substr($row, strrpos($row, '`') + 1));
				}
			}
		}
		return $current;
	}

	public function diffStructures($current, $target, $drop=false)
	{
		$sqls = [];
		foreach ($target as $name => $fields)
		{
			if (array_key_exists($name, $current))
			{
				$changes = array();
				foreach ($fields as $key => $field)
				{
					if (array_key_exists($key, $current[$name]))
					{
						if ($current[$name][$key] != $field)
						{
							if (strpos($key, 'index_') === 0)
							{
								$changes[] = 'DROP KEY `' . $key . '`';
								$changes[] = 'ADD ' .  $field . ' /*' . $current[$name][$key] . '*/';
							}
							else
							{
								$changes[] = 'MODIFY COLUMN `' . $key . '` ' .  $field . ' /*' . $current[$name][$key] . '*/';
							}
						}
						unset($current[$name][$key]);
					}
					else
					{
						$changes[] = 'ADD ' . (strpos($key, 'index_') === 0 ? '' : 'COLUMN `' . $key . '` ') .  $field;
					}
				}
				foreach ($current[$name] as $key => $field)
				{
					$changes[] = 'DROP ' . (strpos($key, 'index_') === 0 ? 'KEY' : 'COLUMN') . ' `' . $key . '`';
				}
				if (!empty($changes))
				{
					$sqls[$name] = 'ALTER TABLE ' . $name . ' ' . PHP_EOL . implode(',' . PHP_EOL, $changes);
				}
				else
				{
					//$sqls[$name] = '-- OK';
				}
				unset($current[$name]);
			}
			else
			{
				$rows = array();
				foreach ($fields as $key => $field)
				{
					if (strpos($key, 'index_') === 0)
					{
						$rows[] = $field;
					}
					else
					{
						$rows[] = '`' . $key . '` ' .  $field;
					}
				}
				$sqls[$name] = 'CREATE TABLE ' . $name . ' (' . PHP_EOL . implode(',' . PHP_EOL, $rows) . PHP_EOL . ') ENGINE=InnoDB DEFAULT CHARSET=utf8';
			}
		}
		if ($drop) {
		    foreach ($current as $name => $fields) {
		        $sqls[$name] = 'DROP TABLE ' . $name;
		    }
		}
		return $sqls;
	}
	
	public function dump(array $tables = null, $structure = true, $data = true)
	{
		$dump = array();
		if ($tables === null)
		{
			$tables = $this->getTables();
		}
		foreach ($tables as $oldtable => $newtable)
		{
			if (is_numeric($oldtable))
			{
				$oldtable = $newtable;
			}
			if ($structure)
			{
				$dump[] = 'DROP TABLE IF EXISTS ' . $newtable . ';';
				$create = $this->db->fetchRow('SHOW CREATE TABLE ' . $oldtable);
				$dump[] = str_replace($oldtable, $newtable, $create['Create Table']) . ';';
			}
			if ($data)
			{
				$offset = 0;

				$dump[] = 'DELETE FROM ' . $newtable . ';';
				while ($rows = $this->db->fetchAll('SELECT * FROM ' . $oldtable . ' LIMIT ' . $offset . ',1000'))
				{
					$columns = array();
					foreach(array_keys(current($rows)) as $column)
					{
						$columns[] = '`' . $column . '`';
					}
					$dump[] = 'INSERT INTO ' . $newtable . '(';
					$dump[] = implode(',', $columns);
					$dump[] = ') VALUES ';
					$i = 0;
					foreach ($rows as $row)
					{
						$r = '(';
						foreach ($row as $val)
						{
							$r .= $this->db->quote($val);
							$r .= ',';
						}
						$r[strlen($r) - 1] = ')';
						if (++$i < count($rows))
						{
							$r .= ',';
						}
						$dump[] = $r;
					}
					$dump[] = ';';
					$offset += 1000;
				}
			}
		}
		return implode(NL, $dump);
	}
	
}
