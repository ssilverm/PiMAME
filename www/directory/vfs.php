<?php
/*
 * $Id: vfs.php 27 2010-07-21 00:08:57Z veghead $
 */

/**
  * @brief Virtual Filesystem class.
  * vfs backends should subclass and implement 
  * the virtual methods
  */
abstract class oliver_vfs {
    var $conf='';
    var $cid;
    var $types;
    var $mime_extension_map;
    var $user;
    var $pass;

    function __construct($conf) 
    {
        $this->conf = $conf;
        $this->cid = 0;
        $this->mime_extension_map = array(
            'Z'       => 'application/x-compress',
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'aifc'    => 'audio/x-aiff',
            'aiff'    => 'audio/x-aiff',
            'asc'     => 'text/plain',
            'asf'     => 'video/x-ms-asf',
            'asx'     => 'video/x-ms-asf',
            'au'      => 'audio/basic',
            'avi'     => 'video/x-msvideo',
            'bcpio'   => 'application/x-bcpio',
            'bin'     => 'application/octet-stream',
            'bmp'     => 'image/bmp',
            'cdf'     => 'application/x-netcdf',
            'class'   => 'application/octet-stream',
            'cpio'    => 'application/x-cpio',
            'cpt'     => 'application/mac-compactpro',
            'csh'     => 'application/x-csh',
            'css'     => 'text/css',
            'dcr'     => 'application/x-director',
            'diff'    => 'text/diff',
            'dir'     => 'application/x-director',
            'dll'     => 'application/octet-stream',
            'dms'     => 'application/octet-stream',
            'doc'     => 'application/msword',
            'dvi'     => 'application/x-dvi',
            'dxr'     => 'application/x-director',
            'eps'     => 'application/postscript',
            'etx'     => 'text/x-setext',
            'exe'     => 'application/octet-stream',
            'ez'      => 'application/andrew-inset',
            'gif'     => 'image/gif',
            'gtar'    => 'application/x-gtar',
            'gz'      => 'application/x-gzip',
            'hdf'     => 'application/x-hdf',
            'hqx'     => 'application/mac-binhex40',
            'htm'     => 'text/html',
            'html'    => 'text/html',
            'ice'     => 'x-conference/x-cooltalk',
            'ics'     => 'text/calendar',
            'ief'     => 'image/ief',
            'ifb'     => 'text/calendar',
            'iges'    => 'model/iges',
            'igs'     => 'model/iges',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'js'      => 'application/x-javascript',
            'kar'     => 'audio/midi',
            'latex'   => 'application/x-latex',
            'lha'     => 'application/octet-stream',
            'log'     => 'text/plain',
            'lzh'     => 'application/octet-stream',
            'm3u'     => 'audio/x-mpegurl',
            'man'     => 'application/x-troff-man',
            'me'      => 'application/x-troff-me',
            'mesh'    => 'model/mesh',
            'mid'     => 'audio/midi',
            'midi'    => 'audio/midi',
            'mif'     => 'application/vnd.mif',
            'mov'     => 'video/quicktime',
            'movie'   => 'video/x-sgi-movie',
            'mp2'     => 'audio/mpeg',
            'mp3'     => 'audio/mpeg',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpga'    => 'audio/mpeg',
            'ms'      => 'application/x-troff-ms',
            'msh'     => 'model/mesh',
            'mxu'     => 'video/vnd.mpegurl',
            'nc'      => 'application/x-netcdf',
            'oda'     => 'application/oda',
            'ogg'     => 'audio/ogg-vorbis',
            'patch'   => 'text/diff',
            'pbm'     => 'image/x-portable-bitmap',
            'pdb'     => 'chemical/x-pdb',
            'pdf'     => 'application/pdf',
            'pgm'     => 'image/x-portable-graymap',
            'pgn'     => 'application/x-chess-pgn',
            'php'     => 'application/x-httpd-php',
            'php3'    => 'application/x-httpd-php3',
            'pl'      => 'application/x-perl',
            'pm'      => 'application/x-perl',
            'png'     => 'image/png',
            'pnm'     => 'image/x-portable-anymap',
            'po'      => 'text/plain',
            'ppm'     => 'image/x-portable-pixmap',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'ps'      => 'application/postscript',
            'qt'      => 'video/quicktime',
            'ra'      => 'audio/x-realaudio',
            'ram'     => 'audio/x-pn-realaudio',
            'ras'     => 'image/x-cmu-raster',
            'rgb'     => 'image/x-rgb',
            'rm'      => 'audio/x-pn-realaudio',
            'roff'    => 'application/x-troff',
            'rpm'     => 'audio/x-pn-realaudio-plugin',
            'rtf'     => 'text/rtf',
            'rtx'     => 'text/richtext',
            'sgm'     => 'text/sgml',
            'sgml'    => 'text/sgml',
            'sh'      => 'application/x-sh',
            'shar'    => 'application/x-shar',
            'shtml'   => 'text/html',
            'silo'    => 'model/mesh',
            'sit'     => 'application/x-stuffit',
            'skd'     => 'application/x-koan',
            'skm'     => 'application/x-koan',
            'skp'     => 'application/x-koan',
            'skt'     => 'application/x-koan',
            'smi'     => 'application/smil',
            'smil'    => 'application/smil',
            'snd'     => 'audio/basic',
            'so'      => 'application/octet-stream',
            'spl'     => 'application/x-futuresplash',
            'src'     => 'application/x-wais-source',
            'stc'     => 'application/vnd.sun.xml.calc.template',
            'std'     => 'application/vnd.sun.xml.draw.template',
            'sti'     => 'application/vnd.sun.xml.impress.template',
            'stw'     => 'application/vnd.sun.xml.writer.template',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc'  => 'application/x-sv4crc',
            'swf'     => 'application/x-shockwave-flash',
            'sxc'     => 'application/vnd.sun.xml.calc',
            'sxd'     => 'application/vnd.sun.xml.draw',
            'sxg'     => 'application/vnd.sun.xml.writer.global',
            'sxi'     => 'application/vnd.sun.xml.impress',
            'sxm'     => 'application/vnd.sun.xml.math',
            'sxw'     => 'application/vnd.sun.xml.writer',
            't'       => 'application/x-troff',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-tex',
            'texi'    => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz'     => 'application/x-gtar',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'tr'      => 'application/x-troff',
            'tsv'     => 'text/tab-separated-values',
            'txt'     => 'text/plain',
            'ustar'   => 'application/x-ustar',
            'vcd'     => 'application/x-cdlink',
            'vcf'     => 'text/x-vcard',
            'vcs'     => 'text/calendar',
            'vfb'     => 'text/calendar',
            'vrml'    => 'model/vrml',
            'vsd'     => 'application/vnd.visio',
            'wav'     => 'audio/x-wav',
            'wax'     => 'audio/x-ms-wax',
            'wbmp'    => 'image/vnd.wap.wbmp',
            'wbxml'   => 'application/vnd.wap.wbxml',
            'wm'      => 'video/x-ms-wm',
            'wma'     => 'audio/x-ms-wma',
            'wmd'     => 'application/x-ms-wmd',
            'wml'     => 'text/vnd.wap.wml',
            'wmlc'    => 'application/vnd.wap.wmlc',
            'wmls'    => 'text/vnd.wap.wmlscript',
            'wmlsc'   => 'application/vnd.wap.wmlscriptc',
            'wmv'     => 'video/x-ms-wmv',
            'wmx'     => 'video/x-ms-wmx',
            'wmz'     => 'application/x-ms-wmz',
            'wrl'     => 'model/vrml',
            'wvx'     => 'video/x-ms-wvx',
            'xbm'     => 'image/x-xbitmap',
            'xht'     => 'application/xhtml+xml',
            'xhtml'   => 'application/xhtml+xml',
            'xls'     => 'application/vnd.ms-excel',
            'xml'     => 'application/xml',
            'xpm'     => 'image/x-xpixmap',
            'xsl'     => 'text/xml',
            'xwd'     => 'image/x-xwindowdump',
            'xyz'     => 'chemical/x-xyz',
            'zip'     => 'application/zip'
        );
    }


    function login($user,$pass) 
    {
        return false;
    }

    function get_pwd()
    {
        return '';
    }

    function chmod($file,$mode)
    {
        return false;
    }

    function chdir($pwd)
    {
        return false;
    }

    function rmdir($todel)
    {
        return false;
    }

    function cdup()
    {
        return false;
    }

    function get($local_file,$file,$fl)
    {
        return false;
    }

    function put($local_file,$file,$fl)
    {
        return false;
    }

    function delete($todel)
    {
        return false;
    }

    function ls($dir)
    {
        return '';
    }

    function size($name)
    {
        return 0;
    }

    function mkdir($name)
    {
        return false;
    }

    function quit()
    {
        return false;
    }

    
    function getFilename($rawLine,$mode) 
    {
        // Remove everything but the filename (hopefully)
        $namemask = '/^..........\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+\S+\s/';
        $pa = preg_replace($namemask,'',$rawLine);
    
        // do we want the real filename if it's a symlink ? 
        if ($this->getFileType($rawLine) == 'l') {
    
            if ($mode==1) {
                $pa = preg_replace('/ \->.*/','',$pa);
            } elseif ($mode==2) {
                $pa = preg_replace('/^.* -> /','',$pa);
            }
        }
        return $pa;
    }
    
    
    function getOwner($rawLine) 
    {
        $owner = preg_replace('/^\S+\s+\S+\s+(\S+)\s.*/','$1',$rawLine);
        return $owner;
    }
    
    
    function getMIMEType($rawLine) 
    {
        if (isset($this->mime_extension_map[$this->getFileExt($rawLine)])) {
            return $this->mime_extension_map[$this->getFileExt($rawLine)]  ;
        }
        echo $this->mime_extension_map['bin'];
        return '';
    }

    
    function FileTam($rawLine) {
        global $conf;
    
        $pa = $rawLine;
        if ($pa < 0) {
            return $conf['nls']['folder'];
        } else {
            if ($pa > 1000) {
                $ntam = ceil($pa/1024);
                if ($ntam > 1000) {
                    $ntam = ceil($ntam/1024).' MB';
                } else {
                    $ntam = $ntam.' KB';
                }
            } else {
                $ntam = $pa.' bytes';
            }
            return $ntam;
        }
    }
    
    
    function getFileDate($rawLine) {
        $lm_unix = ftp_mdtm($this->cid,$this->getFilename($rawLine,0));
        $pa = strftime($this->conf['nls']['date_format'] ,$lm_unix);
        return $pa;
    }
    
    
    
    function getFileExt($rawLine) {
        $ext='';
        if (preg_match('/[^\.\s]+\.([^\.\s]{1,6})$/',$rawLine,$matches)) {
            $ext = $matches[1];
        }
        return $ext;
    }
    
    
    function getFileMode($rawLine) 
    {
        return(substr($rawLine,1,9));
    }
    
    
    function getFileType($rawLine) 
    {
        return(substr($rawLine,0,1));
    }
    
    
    
    function getFileTypeIcon($rawLine) 
    {
        $ext = $this->getFileExt($rawLine);
        $filetype = $this->getFileType($rawLine);

        $img = '';
        if ($filetype == 'd' || $filetype == 'l') {
            $img = '<img src="'.$this->conf['imgdir'].'/folder.png" alt="">';
        } elseif (isset($this->conf['icons'][strtolower($ext)])) {
            $img = '<img src="'.$this->conf['imgdir'].'/'.$this->conf['icons'][strtolower($ext)].'" width="20" height="22" alt="">';
        } else {
            $img = '<img src="'.$this->conf['imgdir'].'/unknown.gif" width="20" height="22" border="0" alt="">';
        }
        return $img;
    }

    
    function showFile($file, $mimetype, $local_file) 
    {
        // set expiration time 
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header("Content-type: $mimetype");
        $filename = preg_replace('/\s+/', '_', $file);
        header("Content-Disposition: filename=$filename");
        header('Content-Length: '.$this->size($file));
        if ($this->conf['hascurl']) {
            $this->getPipe($file);
            exit;
        } else {
           if (@$this->get($local_file,$file,FTP_BINARY)) {
               $fd = fopen($local_file,'rb');
               while (!feof($fd)) {
                   echo fread($fd,8192);
               }
               fclose($fd);
               unlink($local_file);
               exit;
           }
        }
    }
}
?>
