<?php

/** @Command(helpText='uses current db connection to check for database differences') */
class Command_Migrate extends Action_Command
{

    /** @Param(type='bool', default=false, helpText='If set, migrate will ignore
     * current db structure and generate the create statements') */
    public $initial;

    /** @Param(type='bool', default=false, helpText='This command will ask if it
     * shall run generated alters one by one') */
    public $interactive;

    /** @Param(type='bool', default=false, helpText='If set, this command will
     * also generate the DROP TABLE queries') */
    public $drop;

    public function run()
    {
        $tables = array();
        foreach (Eve::getDescendants('Entity') as $entityClass) {
            $method = new ReflectionMethod($entityClass, 'getFields');
            $method->setAccessible(true);
            $fields = $method->invoke(null);
            $method = new ReflectionMethod($entityClass, 'getTableName');
            $method->setAccessible(true);
            $tableName = $method->invoke(null);
            $tables[$tableName] = array();
            foreach ($fields as $key => $field) {
                $definition = $field->getDbDefinition();
                foreach ($definition as $subkey => $def) {
                    $tables[$tableName][$subkey] = $def;
                }
            }
            // TODO indexes!
            $tables[$tableName]['index_primary'] = 'PRIMARY KEY (`id`)';
        }
        $db = new Db(Eve::config('db'));
        $tools = new db_Tools($db);
        $sqls = $tools->diffStructures($this->initial ? array() : $tools->getStructure(), $tables, $this->drop);
        if (empty($sqls)) {
            print 'no differences in database found' . NL;
        } else {
            foreach ($sqls as $key => $q) {
                $sql = '-- ' . $key . NL;
                $sql .= $q . ';' . NL;
                $sql .= NL;
                print $sql;
                if ($this->interactive) {
                    print 'Do you want to run this query now[y/n]?' . NL;
                    if ($this->readChar() == 'y') {
                        print 'RUNNING' . NL;
                        $db->query($sql);
                    }
                }
            }
        }
    }
}

/* TODO xref many to many relations / versioned entities / indexes

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
*/