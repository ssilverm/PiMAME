<?php
/*
 * $Id: dl.php 13 2010-04-13 01:07:08Z veghead $
 */
require_once('config-core.php');
require_once('functions-inc.php');
require_once('vfs-'.$conf['vfstype'].'.php');

$error_message='';

// The filename
$fn = (! empty($_GET['f'])) ? $_GET['f'] : 0;
$parts = pathinfo(urldecode($fn));
$pwd = $parts['dirname'];
$file = $parts['basename'];
$user = $_SERVER['PHP_AUTH_USER'];
$passwd = $_SERVER['PHP_AUTH_PW'];

// Create vfs object - also connects
$wvfs = eval('return new oliver_vfs_'.$conf['vfstype'].'($conf);');
$conn_id=$wvfs->cid;


// Try a login
if ((!($user && $passwd)) || (! $pwd) || (! $file)) {
    noauth('Please supply a username and password');
}

$login_result = $wvfs->login($user, $passwd);


// Could we connect ?
if ((!$conn_id) || (!$login_result)) {
    noauth("That username/password did not allow access. Please try again.");
}

$pwd = $pwd;
$wvfs->chdir($pwd);

$local_file = tempnam('ftp_cache','TMPODL');

$mimetype = 'application/force-download';
$wvfs->showFile($file, $mimetype, $local_file);
exit;



function noauth($reason)  {
    global $conf;
    header('WWW-authenticate: basic realm="'.$conf['app_title'].'"');
    header('HTTP/1.0 401 Unauthorized');
    ?>
<html>
<head>
<title>Authorisation Required</title>
<link rel="stylesheet" href="oliver.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1>Authorisation Required</h1>
<i><?php echo $reason ?></i>
</body>
</html>
    <?php
    exit;
} 


function bomb($title,$desc)  {
    ?>
<html>
<head>
<title><?php echo $title ?></title>
<link rel="stylesheet" href="oliver.css">
</head>
<body bgcolor="#ffffff" text="#000000">
<h1><?php echo $title ?></h1>
<i><?php echo $desc ?></i>
</body>
</html>
    <?php
    exit;
}
