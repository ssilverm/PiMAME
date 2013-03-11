<?php
/*
 * $Id: vfs-ftp.php 18 2010-04-30 19:12:36Z veghead $
 */
require_once('vfs.php');

class oliver_vfs_ftp extends oliver_vfs {

    function __construct($conf) 
    {
        parent::__construct($conf);
        $this->conf=$conf;
        $this->cid=@ftp_connect($this->conf['ftp_server'],
                    $this->conf['ftp_server_port'],
                    $this->conf['ftp_timeout']);
    }


    function login($user,$pass) 
    {
        if (! ($res=@ftp_login($this->cid,$user,$pass))) {
            return($res);
        }
        @ftp_pasv($this->cid,$this->conf['ftp_passive']);
        $this->user = $user;
        $this->pass = $pass;
        return($res);
    }

    function get_pwd()
    {
        return(@ftp_pwd($this->cid));
    }

    function chmod($file,$mode)
    {
        return(@ftp_site($this->cid,"chmod $mode ".$file));
    }

    function chdir($pwd)
    {
        return(@ftp_chdir($this->cid,$pwd));
    }

    function rmdir($todel)
    {
        return(@ftp_rmdir($this->cid,$todel));
    }

    function cdup()
    {
        return(@ftp_cdup($this->cid));
    }

    function get($local_file,$file,$fl)
    {
        return(@ftp_get($this->cid,$local_file,$file,$fl));
    }

    function makeurl($file)
    {
        $url = 'ftp://'.$this->user.':'.$this->pass.'@';
        $url .= $this->conf['ftp_server'].':'.$this->conf['ftp_server_port'].'/';
        // The trailing slash above tell curl to use an absolute url.
        $url .= $this->get_pwd().'/'.$file;
        return $url;
    }

    function getPipe($file)
    {
        if (! $this->conf['hascurl']) {
            return false;
        }
        $url = $this->makeurl($file);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_BINARYTRANSFER => true,
            CURLOPT_URL => $url));
          
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }

    function put($local_file,$file,$fl)
    {
        return(@ftp_put($this->cid,$local_file,$file,$fl));
    }

    function delete($todel)
    {
        return(@ftp_delete($this->cid,$todel));
    }

    function ls($dir)
    {
        $f = empty($this->conf['ls_flags']) ? '' : $this->conf['ls_flags'].' ';
        if (isset($this->conf['space_in_filename_workaround']) && $this->conf['space_in_filename_workaround']) {
            $pwd = @ftp_pwd($this->cid);
                    @ftp_chdir($this->cid,$dir);
                    $list = @ftp_rawlist($this->cid,$f.'.');
                    @ftp_chdir($this->cid,$pwd);
        } else {
            $list=ftp_rawlist($this->cid,$f.$dir);
        }
        return($list);
    }

    function size($name)
    {
        return(@ftp_size($this->cid,$name));
    }

    function mkdir($name)
    {
        return(@ftp_mkdir($this->cid,$name));
    }

    function quit()
    {
        return(@ftp_quit($this->cid));
    }

    
}
?>
