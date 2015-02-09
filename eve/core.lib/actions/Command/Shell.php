<?php

/** @Command(helpText='runs a simple interactive PHP shell') */
class Command_Shell extends Action_Command
{

    public function run()
    {
        while (! feof(STDIN)) {
            print 'php# ';
            eval($this->readLine());
            /* TODO a bit smarter mode
             * print empty($cmd) ? '# ' : '..';
             * $cmd .= $this->readLine() . NL;
             * ob_start();
             * if (eval($cmd) !== false) {
             * ob_end_flush();
             * $cmd = '';
             * } else {
             * ob_end_clean();
             * } */
        }
    }
}