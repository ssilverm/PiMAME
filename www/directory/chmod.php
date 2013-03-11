<?php
/* 
 * $Id: chmod.php 8 2010-04-08 19:39:44Z veghead $
 */

if (!$conf['enable_chmod']) {
    exit;
}


if ($file) {
    // The posted variables are effectively urlencoded twice.
    // The querystring ones are only urlencoded once, so for
    // simplicity we urlencode the querystring filename
    $fileactions[urlencode($file)]=urlencode($file);
} else {
    $fileactions=(! empty($_POST['cb'])) ? $_POST['cb'] : array();
}

require_once('header-inc.php');


?>
<p><b><?php echo $conf['nls']['path'] ?></b><?php echo $pwd ?></p>
<center><table border="0" cellspacing="0" cellpadding="1" width="100%">
<tr><td class="tableborder">
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">

<table bgcolor="#ffffff" border="0" cellspacing="2" cellpadding="2" width="100%">
<!-- content -->

<tr class="odd">
<td><?php echo $conf['nls']['file_name'] ?>
<?php
foreach($fileactions as $key => $value) {
    print("<input type=\"hidden\" name=\"cb[".$key);
    print("]\" value=\"".$key."\">\n");
}
?>
<input type="hidden" name="pwd" value="<?php echo $pwd ?>">
<input type="hidden" name="file" value="<?php echo $file ?>">
</td>
<td colspan="3"><?php echo $conf['nls']['user']." ".$conf['nls']['permissions']?></td>
<td colspan="3"><?php echo $conf['nls']['group']." ".$conf['nls']['permissions']?></td>
<td colspan="3"><?php echo $conf['nls']['others']." ".$conf['nls']['permissions']?></td>
</tr>

<tr class="even">
<td><b><?php

if ($file) {
    print(htmlentities($file));
} elseif (count($fileactions)==1) {
    reset($fileactions);
    print(htmlentities(urldecode(key($fileactions))));
} else {
    print ("<b>".$conf['nls']['multiple_files']."</b>");
}

?></b></td>
<?php
$boxes="";
for($i=0;$i<9;$i++) {
    $ticked = (substr($mode,$i,1)!='-') ? " checked" : "";
    $pval = pow(2,8-$i);
    $boxes .= "<td><input type=\"checkbox\" name=\"p$i\" value=\"$pval\" $ticked></td>\n";
}
print $boxes;
?>
</tr>


<tr class="odd">
<td colspan="10"><center><input type="submit" name="actchmod" value="<?php echo $conf['nls']['change_perms'] ?>">&nbsp;<input type="submit" name="actcancel" value="<?php echo $conf['nls']['cancel'] ?>"></center></td>
</tr>

<!-- content -->
</table>
</form>
</td>
</tr>
</table>
<?php
require_once('footer-inc.php');
exit;
?>
