<html>
<head>
<title>Oliver - installation check</title>
<style type="text/css">
body {
    font-family: "Andale Mono", monospace;
}

.good {
    color:green;
}
.bad {
    color:red;
}
.warning {
    color:#ff9900;
}
</style>
</head>
<body>
<h1>Oliver Installation Check</h1>
<!--
<?php 
if (false) {
?>--><h1>PHP is not enabled!</h1>
Please ensure php and apache are configured properly.
<!--<?php
} else {
    echo '--';
    echo chr(62);
    echo 'PHP enabled :',report(1);
    echo 'PHP FTP support :',report(function_exists('ftp_connect') ? 1 : 0,
        'you do not appear to have FTP support in your version of PHP.' .
        'Most Linux distributions will have a package (usually called something'.
        ' like "php5-ftp") that you can install to fix this.');
    echo 'Permissions :',report((fopen('ftp_cache/f','w') != false) ? 1 : 0,
        'Please allow your web server write access to ftp_cache. See INSTALL.');
    include('config-core.php');
    if (file_exists('config.php')) {
        echo 'FTP connect :',report(ftp_connect($conf['ftp_server'],$conf['ftp_server_port']) ? 1 : 0,
        "Can't connect - check your FTP settings");
    }
    echo 'TLS/SSL          :',report((empty($_SERVER['HTTPS']) ? 2 : 1),
        'You do not have TLS/SSL enabled. This means you will be at risk '.
        'from eavesdroppers. Configure your webserver for secure (https) access.');

    echo chr(60),'br /',chr(62);
    echo chr(60),'br /',chr(62);
    echo chr(60),'span class="good"',chr(62),'This looks good to go!',chr(60),'/span',chr(62);
    echo chr(60),'br /',chr(62);
    if (!file_exists('config.php')) {
        echo chr(60),'em',chr(62),'To get started, copy "config-dist.php" to "config.php"';
        echo chr(60).'/em',chr(62);
    }

}


function report($r, $reason = '')
{
    switch($r) {
    case(1):
    default: 
        $class = 'good';
        $status = 'OK';
        break;
    case(2):
        $class = 'warning';
        $status = 'Warning';
        break;
    case(0):
        $class = 'bad';
        $status = 'Failed';
        break;

    }
    echo chr(60),'span class="';
    echo $class;
    echo '"',chr(62);
    echo $status;
    echo chr(60),'/span',chr(62);
    echo chr(60),'br /',chr(62);
    if ($r != 1) {
        echo $reason;
        echo chr(60),'/body',chr(62);
        echo chr(60),'/html',chr(62);
        if ($r == 0) exit;
    }
}

?>
</body>
</html>
