<?php
/* 
 * $Id: functions-inc.php 8 2010-04-08 19:39:44Z veghead $
 */


function writeLog($logmsg)
{
    global $conf;
    if (! $conf['enable_logging']) {return;}
    if ($conf['logfile']=="syslog") {
        syslog(LOG_INFO,$logmsg);
    } else {
        if (!($fp=fopen($conf['logfile'], "a"))) {
            return;
        }
        $now=date("Y-d-m H:i:s");
        fputs($fp, "$now $logmsg\n");
        fclose($fp);
    }
}


?>
