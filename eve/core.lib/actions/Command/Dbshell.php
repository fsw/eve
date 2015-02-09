<?php

/** @Command(helpText='runs an interactive MySQL shell using connection info from config') */
class Command_Dbshell extends Action_Command
{
    
    // TODO parameter for db connection
    public function run()
    {
        $db = new Db(Eve::config('db'));
        while (! feof(STDIN)) {
            print 'sql# ';
            $cmd = $this->readLine();
            if ($cmd) {
                $ret = $db->fetchAll($cmd);
                if (empty($ret)) {
                    print 'EMPTY' . NL;
                } else {
                    $cols = array_keys($ret[0]);
                    foreach ($cols as $key) {
                        printf("|%20.20s", $key);
                    }
                    printf("|\n");
                    foreach ($ret as $row) {
                        foreach ($cols as $key) {
                            printf("|%20.20s", $row[$key]);
                        }
                        printf("|\n");
                    }
                }
            }
        }
        print NL;
    }
}