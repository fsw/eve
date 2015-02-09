<?php

/** @Command(helpText='loads predefined entities to database') */
class Command_Load extends Action_Command
{

    /** @Param(type='string', helpText='Name of the fixture file to load.
     * Command will search available libs roots for "fixtures/<fixture>.json
     * file') */
    public $fixture;

    public function run()
    {
        $path = 'fixtures' . DS . $this->fixture . '.json';
        $realPath = Eve::findFile($path);
        if ($realPath === null) {
            throw new Exception('Can\'t find fixture file ' . $path);
        }
        // TODO handle JSON exceptions
        $data = json_decode(file_get_contents($realPath), true);
        
        $added = 0;
        $updated = 0;
        $unchanged = 0;
        
        foreach ($data as $row) {
            $entityClass = $row['class'];
            $fields = $row['fields'];
            // var_dump($row);
            if ($row['identify_by']) {
                $getByFields = [];
                foreach (explode(',', $row['identify_by']) as $field) {
                    $getByFields[$field] = $fields[$field];
                }
                $current = $entityClass::getByFields($getByFields);
                if ($current === null) {
                    print '+';
                    $added ++;
                    $new = new $entityClass();
                    $new->updateWithArray($fields);
                    var_dump($new);
                    // TODO $entityClass::getFields();
                    $new->save();
                } else {
                    $current->updateWithArray($fields);
                    // update
                    print '!';
                    $updated ++;
                    $current->save();
                }
            }
        }
        print NL;
        print NL;
        print 'added: ' . $added . NL;
        print 'updated: ' . $updated . NL;
        print 'unchanged: ' . $unchanged . NL;
    }
}