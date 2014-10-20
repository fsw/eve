<?php

class Action_Migrate extends Action
{	
	public function run()
	{
		$tables = array();
		foreach (Eve::getDescendants('Entity') as $entityClass)
		{
		      $method = new ReflectionMethod($entityClass, 'getFields');
		      $method->setAccessible(true);
		      $fields = $method->invoke(null);
		      $method = new ReflectionMethod($entityClass, 'getTableName');
		      $method->setAccessible(true);
		      $tableName = $method->invoke(null);
		      $tables[$tableName] = array();
		      foreach ($fields as $key => $field)
		      {
				$definition = $field->getDbDefinition();
				foreach ($definition as $subkey => $def) {
				      $tables[$tableName][$subkey] = $def;
				}
		      }
		      //TODO!
		      $tables[$tableName]['index_primary'] = 'PRIMARY KEY (`id`)';

		      //var_dump($tableName, $fields);
		      //$tables = array_merge($tables, $entity::_getDbStructure());
		}
		$db = new Db(['dsn' => EVE_DB_DSN, 'user' => EVE_DB_USER, 'pass' => EVE_DB_PASS]);
		$tools = new db_Tools($db);
		$sqls = $tools->diffStructures($tools->getStructure(), $tables);
		//var_dump($tools->getStructure(), $tables);
		//var_dump($sqls);
		foreach ($sqls as $key => $q)
		{
			$sql = '-- ' . $key . NL;
			$sql .= $q . ';' . NL;
			echo nl2br($sql);
			$db->query($sql);
		}
		//die('MIGRATE');
	}
}


/*
public static function _getDbStructure()
	{
		$ret = array();
		$ret[static::getTableName()] = array();
		foreach (static::getFields() as $key => $field)
		{
			if ($field instanceof field_relation_Many)
			{
				$f = new field_Number();
				$ret[static::getTableName() . '_xref_' . $key] = array(
						static::getBaseName() . '_id' => $f->getDbDefinition(),
						$key . '_id' => $f->getDbDefinition()
				);
			}
			elseif ($field instanceof field_relation_Container)
			{
	
			}
			else
			{
				$definition = $field->getDbDefinition();
				if (is_array($definition))
				{
					foreach ($definition as $subkey => $def)
					{
						$ret[static::getTableName()][$key . '_' . $subkey] = $def;
					}
				}
				else
				{
					$ret[static::getTableName()][$key] = $definition;
				}
			}
		}
		if (!empty(static::$versioned))
		{
			$ret[static::getRevisionsTableName()] = static::getRevisionsTableStructure($ret[static::getTableName()]);
		}
		foreach (static::getIndexes() as $key => $field)
		{
			$unique = ($key == 'primary') || array_shift($field);
			$ret[static::getTableName()]['index_' . $key] =
			($key == 'primary' ? 'PRIMARY KEY' : (($unique ? 'UNIQUE ' : '') . 'KEY ' . '`index_' . $key . '`'))
			. ' (`' . implode($field , '`,`') . '`)';
		}
		return $ret;
	}
