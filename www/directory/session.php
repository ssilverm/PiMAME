<?php

/**
  */
class OliverSession {
    var $ip;
    var $error_message='';
    var $user;
    var $passwd;
    var $fileactions;
    var $mimetype;
    var $file;
    var $act;
    var $newdir;
    var $download;
    var $pwd;
    var $mode;
    var $tmpFilename;
    var $origFilename;
    var $conf;
    var $setLanguage;
    var $sessionID;
    var $perms;

    function __construct($conf) {
        $this->conf         = $conf;
        $this->ip           = $_SERVER['REMOTE_ADDR'];
        $this->user         = ( isset($_POST['user'])) ? $_POST['user'] : 0;
        $this->passwd       = ( isset($_POST['passwd'])) ? $_POST['passwd'] :0 ;
        $this->fileactions  = ( isset($_POST['cb'])) ? $_POST['cb'] : array();
        $this->mimetype     = ( isset($_REQUEST['mimetype']) ? $_REQUEST['mimetype'] : 0);
        $this->file         = ( isset($_REQUEST['file']) ? $_REQUEST['file'] : 0);
        $this->act          = ( isset($_REQUEST['act'])) ? $_REQUEST['act'] : "NOACTION";
        $this->newdir       = ( isset($_REQUEST['newdir']) ? $_REQUEST['newdir'] : 0);
        $this->download     = ( isset($_REQUEST['d'])) ? 1 : 0;
        if (isset($_FILES['filename'])) {
            $this->tmpFilename = $_FILES['filename']['tmp_name'];
            $this->origFilename = $_FILES['filename']['name'];
        } else {
            $this->tmpFilename = '';
            $this->origFilename = '';
        }
        $this->pwd = (isset($_REQUEST['pwd'])) ? $_REQUEST['pwd'] : '';
        $this->mode = (isset($_REQUEST['mode'])) ? $_REQUEST['mode'] : 'rw-------';
        if (isset($_REQUEST['p0'])) {
            $this->perms = 0;
            foreach($_REQUEST as $key => $value) {
                if (! preg_match('/^p\d+$/', $key)) continue;
                $this->perms += (is_numeric($value) ? $value : 0);
            }
        }

        $this->setLanguage = (isset($_REQUEST['setlanguage']) ? $_REQUEST['setlanguage'] : 0);

        ini_set('session.name',$conf['session_name']);
        session_start();
        $this->sessionID = session_id();

        if ((! $this->setLanguage) && ((! empty($_SESSION['language'])))) {
            $this->setLanguage=$_SESSION['language'];
        }

        if (preg_match('/^\w\w-\w\w$/',$this->setLanguage)) {
            $this->conf['lang'] = $this->setLanguage;
        }

        $this->showlisting = 1;
        $this->sessionvalid = 0;
    }



    function sessionValid($wvfs) {

        // Do we have an active session ?
        if (($this->sessionID) && isset($_SESSION['pad'])) {
            $data_ex = explode('|',$_SESSION['cred']);
            $ftp_user_name = trim($data_ex[0]);
            $encpass = trim($data_ex[1]);
            $ftp_user_pass = $_SESSION['pad']^(base64_decode($encpass));
    
            if (time() - (isset($_SESSION['atime']) ? $_SESSION['atime'] : 0) > $this->conf['session_timeout']) {
                session_destroy();
                $this->error_message = 'Session has expired';
                return false;
            }

            $this->sessionvalid = 1;


        /* 
         * So, no valid session and a blank username and password ?
         * none shall pass
         */
        } elseif (empty($this->user) && empty($this->passwd)) {
            return false;

        /*
         * No active session - but a username and password. Let's give them a go
         */
        } else {
            $ftp_user_name = $this->user;
            $ftp_user_pass = $this->passwd;
        }
    
    
        if (!$wvfs) {
            return false;
        }

        $this->conn_id = $wvfs->cid;
        // Try a login
        $login_result = $wvfs->login($ftp_user_name, $ftp_user_pass);

        if (!$login_result) {
            $this->error_message = 'Login failed';
            return false;
        }

        /* 
         * Yay! They logged in!
         * If the pad isn't set, set it 
         * "pad" sounds better than "nonce" you see...
         */
        if (!$this->sessionvalid) {
            $pad = md5(rand().microtime());
            $_SESSION['pad']=$pad;
            $encpass = base64_encode($pad^$ftp_user_pass);
    
            $_SESSION['cred'] = "$ftp_user_name|$encpass";
            // Log it to remind ourselves that life isn't always bad. 
            writeLog('['.$this->ip."] OK: Logged in: user=$ftp_user_name");
        }
        return true;
    }

    function getParam($name) {
        $value = eval('return $this->'."$name;");
        return $value;
    }

    function getConf() {
        return $this->conf;
    }
}
