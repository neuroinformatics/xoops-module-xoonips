<?php

// $Revision:$
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * directory handling class.
 *
 * @copyright copyright &copy; 2008 RIKEN Japan
 */
class XooNIpsUtilityDirectory extends XooNIpsUtility
{
    /**
     * flag for windows.
     *
     * @var bool
     */
    public $is_windows = false;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->setSingleton();
        $this->is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }

    /**
     * get temorary directory.
     *
     * @return string temorary directory
     */
    public function get_tempdir()
    {
        // try to use sys_get_temp_dir() function ( >= PHP 5.2.1 )
        if (function_exists('sys_get_temp_dir')) {
            $path = $this->realpath(sys_get_temp_dir());
            if ($path !== false && $this->_check_dir_perm($path)) {
                return $path;
            }
        }
        // try to get temporary directory by environment variables
        $envs = array(
        'TMP',
        'TMPDIR',
        'TEMP',
        );
        foreach ($envs as $env) {
            if (!empty($_ENV[$env])) {
                $path = $this->realpath($_ENV[$env]);
                if ($path !== false && $this->_check_dir_perm($path)) {
                    return $path;
                }
            }
        }
        // try to use dirname of tempnam() function result
        $tempfile = @tempnam(uniqid(mt_rand(), true), 'XooNIps');
        if (file_exists($tempfile)) {
            @unlink($tempfile);
            $path = $this->realpath(dirname($tempfile));
            if ($path !== false && $this->_check_dir_perm($path)) {
                return $path;
            }
        }
        // give up
        $this->_fatal_error('failed to get temporary directory path', __LINE__);
    }

    /**
     * get template string for mkdtemp() and mkstemp() method.
     *
     * @param string $prefix
     *
     * @return string created template string
     */
    public function get_template($prefix)
    {
        $template = sprintf('%s/%sXXXXXX', $this->get_tempdir(), $prefix);

        return $template;
    }

    /**
     * create a name for tempoaray file.
     *
     * @param string $dir    temporary directory
     * @param string $prefix prefix name of temporary file
     *
     * @return string created temporary file name
     */
    public function tempnam($dir, $prefix)
    {
        if (empty($dir) || !$this->_check_dir_perm($dir)) {
            $dir = $this->get_tempdir();
        }
        $template = sprintf('%s/%sXXXXXX', $dir, $prefix);
        $fp = $this->_mktemp_body($template, true);
        if ($fp === false) {
            return false;
        }
        fclose($fp);

        return $template;
    }

    /**
     * create a unique tempoaray file.
     *
     * @param string &$template template of temporary directory
     *
     * @return resource created temporary file resource
     */
    public function mkstemp(&$template)
    {
        return $this->_mktemp_body($template, true);
    }

    /**
     * create a unique tempoaray directory.
     *
     * @param string &$template template of temporary directory
     *
     * @return bool false if failure
     */
    public function mkdtemp(&$template)
    {
        return $this->_mktemp_body($template, false);
    }

    /**
     * make directory recursive (like 'mkdir -p' command).
     *
     * @param string $path directory path
     * @param int    $mode creation directory mode
     *
     * @return bool false if failure
     */
    public function mkdir($path, $mode)
    {
        // clear file status caches
        clearstatcache();
        // normalize directory name
        $path = $this->_normalize_dirname($path);
        // create parent directory if not exists
        $mydirname = dirname($path);
        if (!is_dir($mydirname)) {
            if (!$this->mkdir($mydirname, $mode)) {
                // failed to create parent directory
                return false;
            }
        }
        if (is_dir($path)) {
            // already exists
            return true;
        }

        return @mkdir($path, $mode);
    }

    /**
     * remove directory recursive (like 'rm -rf' command).
     *
     * @param string $path directory path
     *
     * @return bool false if failure
     */
    public function rmdir($path)
    {
        // clear file status caches
        clearstatcache();
        // normalize directory name
        $path = $this->_normalize_dirname($path);
        if (!is_dir($path)) {
            // not directory
            return false;
        }
        if (is_link($path)) {
            // unlink symblic link
            return @unlink($path);
        }
        if ($dh = opendir($path)) {
            while (($sf = readdir($dh)) !== false) {
                if ($sf == '.' || $sf == '..') {
                    continue;
                }
                $subpath = $path.'/'.$sf;
                if (is_dir($subpath)) {
                    // directory
                    if (!$this->rmdir($subpath)) {
                        // failed to delete sub directory
                              return false;
                    }
                } else {
                    // file
                    if (@unlink($subpath) === false) {
                        // failed to remove file
                              return false;
                    }
                }
            }
            closedir($dh);
        }

        return @rmdir($path);
    }

    /**
     * get real path.
     *
     * @return string real path
     */
    public function realpath($path)
    {
        $path = realpath($path);
        if ($path === false) {
            // failed to resolv real path
            return $path;
        }

        return $this->_normalize_dirname($path);
    }

    /**
     * normalize directory path
     *  - use '/' file separator for windows
     *  - remove '/' from tail if given.
     *
     * @param string $path
     *
     * @return string normalized directory path
     */
    public function _normalize_dirname($path)
    {
        if ($this->is_windows) {
            // use '/' for file separator for windows platform
            $path = str_replace('\\', '/', $path);
        }
        // remove '/' from tail if given
        if (substr($path, -1) == '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }

        return $path;
    }

    /**
     * tells whether the filename is writable.
     *
     * @param string $path
     *
     * @return bool false if writable
     * @see:   http://www.php.net/manual/en/function.is-writable.php
     */
    public function _is_writable($path)
    {
        if (!$this->is_windows) {
            // it works if not IIS
            return is_writable($path);
        }
        //will work in despite of Windows ACLs bug
        //NOTE: use a trailing slash for folders!!!
        //see http://bugs.php.net/bug.php?id=27609
        //see http://bugs.php.net/bug.php?id=30931
        if ($path[strlen($path) - 1] == '/') {
            // recursively return a temporary file path
            return $this->_is_writable($path.uniqid(mt_rand()).'.tmp');
        } elseif (is_dir($path)) {
            return $this->_is_writable($path.'/'.uniqid(mt_rand()).'.tmp');
        }
        // check tmp file for read/write capabilities
        $rm = file_exists($path);
        $f = @fopen($path, 'a');
        if ($f === false) {
            return false;
        }
        fclose($f);
        if (!$rm) {
            unlink($path);
        }

        return true;
    }

    /**
     * check writable directory permission.
     *
     * @param string $path
     *
     * @return bool false if not temporary directory
     */
    public function _check_dir_perm($path)
    {
        // use real path
        $path = $this->realpath($path);
        if ($path === false) {
            // path not found
            return false;
        }
        if (!is_dir($path)) {
            // not direcotry
            return false;
        }
        if (!$this->_is_writable($path) || (!$this->is_windows && (!is_readable($path) || !is_executable($path)))) {
            // not operatable permission
            return false;
        }

        return true;
    }

    /**
     * create temporary file or direcotry.
     *
     * @param string &$path   path string, last 6 char must be 'XXXXXX'
     * @param bool   $is_file true if create file, false if create directory
     *
     * @return mixed
     *               - false: failure
     *               - resource: created file resource
     *               - true: success to create directory
     */
    public function _mktemp_body(&$path, $is_file)
    {
        // clear file status caches
        clearstatcache();
        // use absolete path
        $mydirname = $this->realpath(dirname($path));
        if ($mydirname === false || !$this->_check_dir_perm($mydirname)) {
            // parent directory not found or invalid permission
            return false;
        }
        $fpath = $mydirname.'/'.basename($path);
        // get base name of path
        if (!preg_match('/(.+)XXXXXX$/', $fpath, $matches)) {
            // doesn't supply XXXXXX suffix
            return false;
        }
        $prefix = $matches[1];

        // try to create file or directory three times
        for ($try = 0; $try < 3; ++$try) {
            $issue = $this->_get_random_issue();
            $fpath = $prefix.$issue;
            if ($is_file) {
                // file creation mode
                if (!file_exists($fpath)) {
                    // open file with O_EXCL|O_CREAT flag
                      $fp = @fopen($fpath, 'xb');
                    if ($fp !== false) {
                        @chmod($fpath, 0600);
                        $path = $fpath;

                        return $fp;
                    }
                }
            } else {
                // directory creation mode
                if (!is_dir($fpath)) {
                    if ($this->mkdir($fpath, 0700)) {
                        $path = $fpath;

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * get random issue of 6 chars.
     *
     * @return string random issue
     */
    public function _get_random_issue()
    {
        static $random_state = false;
        // set random state
        if ($random_state === false) {
            $random_state = getmypid();
        }
        // generate random chars
        $random_state = md5(uniqid(mt_rand().$random_state, true).session_id());
        $random_chars = substr($random_state, 0, 6);

        return $random_chars;
    }

    /**
     * fatal error.
     *
     * @param string $msg  error message
     * @param int    $line line number
     */
    public function _fatal_error($msg, $line)
    {
        if (XOONIPS_DEBUG_MODE) {
            echo '<pre>';
            print_r(debug_backtrace());
            echo '</pre>';
        }
        die('fatal error : '.$msg.' in '.__FILE__.' at '.$line);
    }
}

/*

// example 1: create temporary file
$dirutil =& xoonips_getutility( 'directory' );
$path = '/tmp/XooNIps_XXXXXX';
$fp = $dirutil->mkstemp( $path );
if ( $fp === false ) {
  exit( 'failed to create temp file' );
}
echo 'FILE PATH:'.$path."\n";
fwrite( $fp, "HOGEHOGE" );
...
fclose( $fp );
unlink( $path );

// exampe 2: create temporary directory
$dirutil =& xoonips_getutility( 'directory' );
$path = $dirutil->get_template( 'XooNIps_' );
if ( $dirutil->mkdtemp( $path ) ) {
  exit( 'failed to create temp directory' );
}
echo 'DIRECTORY PATH:'.$path."\n";
...
$dirutil->rmdir( $path );

// example 3: create and delete directory recursive
$dirutil =& xoonips_getutility( 'directory' );
$dirutil->mkdir( '/var/tmp/okumura1/okumura2/okumura3', 0755 );
$dirutil->rmdir( '/var/tmp/okumura1' );

*/
