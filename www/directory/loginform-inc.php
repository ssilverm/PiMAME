<center>
<?php 

if (isset($errmsg)) {
    echo("<b>$errmsg</b>");
}

if (file_exists($conf['motdfile'])) {
    include($conf['motdfile']);
}
?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
<table border=0>
<tr><td>
<?php echo $conf['nls']['username'] ?>
</td><td>
<input name="user" type="text">
</td></tr>
<tr><td>
<?php echo $conf['nls']['password'] ?>
</td><td>
<input name="passwd" type="password">
</td></tr>
<?php
// Do we allow the user to choose their language?

if ($conf['lang_ask']) {
    require_once('lang/lang.php');
    echo '<tr><td>',$conf['nls']['language'],':</td><td>';
    echo '<select name="setlanguage">';

    // Default to en-uk
    $language = isset($_SESSION['language']) ? $_SESSION['language'] : 'en-uk';

    foreach($langlist as $langname => $langfile) {
        $s = ($language == $langfile) ? 'selected' : '';
        echo '<option value="',$langfile,'" ',$s,'>',$langname,'</option>';
    }
    echo "</select>\n</td></tr>\n" ;
}
?>
<tr><td colspan="2" align="center">
<input type="submit" value="<?php echo $conf['nls']['login'] ?>">
</td></tr></table>
</form>
</center>
