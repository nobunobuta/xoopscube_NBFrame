<?php
/**
 *
 * @package NBFrame
 * @version $Id$
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */

// This file is a fake frontend controller to shorten XOOPS Cube Module file URL
// eg. http://foo/modules/simple/ => http://foo/simple ( If this filename is 'simple')
//
// This file and 'preload/ShortURL.class.php' is delivered with NBFrame sample source files,
// but these files ara independent from any other NBFrame libraries,
// so you can use thease two files with any other XOOPS Cube Module.
// Only you need is, renaming this file to your module name and putting it into XOOPS_ROOT_PATH directory
// and then put ShortURL.class.php into XOOPS_ROOT_PATH/preload directory.
//
// You can use this file under Apache HTTP Server environment, with 'AcceptPathInfo On' Directive setting
// and 'Options +MultiViews' being specified with XOOPS Enviroment directory.
//
// If you can't set 'AcceptPathInfo On' in you Apache environment, you can not use this file.
//
// Even if you could not specify 'Options +MultiViews', you can use another shortend URL like
//   http://foo/modules/simple/index.php => http://foo/simple.php/index.php
// (But,with most of Apache Server environments, you can set this option via .htaccess file at XOOPS_ROOT_PATH)
//
// I did not test so much, so it is a just a sample of my idea.
//
//***********************************************************************************************************
// Configuration (Maybe, you may not edit this for trial)
//  or you can override this settings with config file XOOPS_ROOT_PATH/settings/ShortURL_XXXXXX.inc.php
//***********************************************************************************************************

// You can set Dirname directly like following line
//  $NBFrameFrontendConf['dirname']='simple';
$NBFrameFrontendConf['thisname'] = preg_replace('/(.*)(\.php)/','\\1', basename(__FILE__));
$NBFrameFrontendConf['dirname'] = $NBFrameFrontendConf['thisname'];
// Following module Directories should not access via HTTP request.
// These HTTP Stream will send as a static content
$NBFrameFrontendConf['denyDirArray'] = array(
    'action/',
    'admin/blocks',
    'admin/include/',
    'admin/actions/',
    'admin/class/',
    'admin/forms/',
    'admin/templates/',
    'blocks',
    'data/',
    'cache/',
    'class/',
    'forms/',
    'include/',
    'kernel/',
    'preload/',
    'private/',
    'templates/',
    'xoops_version.php',
);

// Any HTML or PHP files in following module directories will be wrapped with XOOPS Theme
$NBFrameFrontendConf['wrapDirArray'] = array(
    'contents/',
);
$NBFrameFrontendConf['wrapExtArray'] = array(
    'html',
    'htm',
    'php',
);

//Follwing extention files will send out via NBFrameHTTPOutput Class
// (Accessing to other extension file cause HTTP redirect);
$NBFrameFrontendConf['mimeArray'] = array(
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'png' => 'image/png',
    'swf' => 'application/x-shockwave-flash',
//  'html' =>'text/html',
    'js'   =>'application/x-javascript',
    'css'  =>'text/css',
);

//If you are in Apache MultiViews environment, you can set this to true
//to avoid HTTP access to this file with php file extension.
$NBFrameFrontendConf['disallowWithPhpExt'] = false;


//If you want to try execute module frontend controller when URL status is 404,
//you can specify PHP file name of this frontend controller (eg. index.php)
$NBFrameFrontendConf['failOver'] = null;

if (file_exists(dirname(__FILE__).'/settings/ShortURL_'.$NBFrameFrontendConf['thisname'].'.inc.php')) {
    include dirname(__FILE__).'/settings/ShortURL_'.$NBFrameFrontendConf['thisname'].'.inc.php';
}

if (!defined('XOOPS_ROOT_PATH')) {
    // Class definition of NBFrameHTTPOutputSubset
    // This class delived from NBFrame Class Libraries
    // But it's independent form any other NBFrame Classes
    if (!class_exists('NBFrameHTTPOutputSubset')) {
        class NBFrameHTTPOutputSubset {
            function putFile($fileName, $contentType) {
                error_reporting(E_ERROR);
                if (file_exists($fileName)) {
                    $fileSize = filesize($fileName);
                    header('Content-Type: '.$contentType);
                    header('Accept-Ranges: bytes');
                    if (isset($_SERVER['HTTP_RANGE'])) {
                        list($dummy, $start, $end) = preg_split("/[=\-]/", $_SERVER["HTTP_RANGE"]);
                        $start = intval($start);
                        if (trim($end)=='') {
                            $end = $fileSize-1;
                        } else {
                            $end = intval($end);
                        }
                    } else {
                        $start = 0;
                        $end = $fileSize-1;
                    }
                    $partial = 0;
                    if (($start != 0) || ($end != ($fileSize-1))) {
                        header('HTTP/1.1 206 Partial Content');
                        header('Status: 206 Partial Content');
                        header('Content-Range: bytes '.$start."-".$end.'/'.$fileSize);
                        $partial = 1;
                    }
                    if ($partial == 1) {
                        $size = $end-$start+1;
                    } else {
                        $size = $fileSize;
                    }
                    header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                    NBFrameHTTPOutputSubset::staticContentHeader(filemtime($fileName), $fileName);
                    while (ob_get_level()) {ob_end_clean();}
                    ob_implicit_flush(true);
                    $handle = fopen($fileName,'rb');
                    fseek($handle, $start);
                    $block = 16384;
                    $content = '';
                    while (!feof($handle)) {
                        print(fread($handle, $block));
                        flush();
                    }
                    exit();
                }
            }

            function staticContentHeader($mod_timestamp, $etag_base='') {
                if (!empty($mod_timestamp)) {
                    $etag = md5($_SERVER["REQUEST_URI"] . $mod_timestamp . $etag_base);
                    header('Pragma:');
                    header('Etag: "'.$etag.'"' );
                    header('Cache-Control:');
                    header('Expires:');
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mod_timestamp).' GMT');
                    if((!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && ($mod_timestamp==NBFrameHTTPOutputSubset::_str2Time($_SERVER['HTTP_IF_MODIFIED_SINCE'])))||
                       (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && ($etag==$_SERVER['HTTP_IF_NONE_MATCH']))){
                        header('HTTP/1.1 304 Not Modified');
                        exit();
                    }
                }
            }

            function _str2Time( $str ) {
                $str = preg_replace( '/;.*$/', '', $str );
                if ( strpos( $str, ',' ) === false )
                    $str .= ' GMT';
                return strtotime( $str );
            }
        }
    }

    if (!function_exists('__NBFrameShorURLParser__')) {
        function __NBFrameShorURLParser__($confArray) {
            if (isset($_SERVER['REQUEST_URI'])) {
                if ($confArray['disallowWithPhpExt'] && preg_match('/^'.preg_quote('/'.basename(__FILE__),'/').'/',$_SERVER['REQUEST_URI'])) {
                    header('HTTP/1.0 404 Not Found');
                    exit('404 Not Found');
                }
                // Force redirect URL eg. http://for/simple to http://for/simple/
            if (!preg_match('/^(\/.*)?'.preg_quote('/'.$confArray['thisname'],'/').'(\.php)?\//',$_SERVER['REQUEST_URI'])) {
                $uri = preg_replace('/^(\/.*)?('.preg_quote('/'.$confArray['thisname'],'/').')(\.php)?/','\\1\\2\\3/', $_SERVER['REQUEST_URI']);
                    header('Location:'. $uri);
                }
                
                // Rewrite Some Server Variables.
                $origRequestUri = $_SERVER['REQUEST_URI'];
                list ($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'], $_SERVER['HTTP_REFERER']) =
                preg_replace('/^(\/.*)?'.preg_quote('/'.$confArray['thisname'],'/').'(\.php)?/','\\1/modules/'.$confArray['dirname'], 
                                 array($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'], $_SERVER['HTTP_REFERER']));

            }

            $queryArray = preg_split('/[?#]/', $origRequestUri);
            $pathArray = array_slice(explode('/', $queryArray[0]),1);
            $i = 0;
            foreach($pathArray as $path) {
                if ($path != $confArray['thisname']) {
                    $i++;
                } else {
                    break;
                }
            }

            $pathArray = array_slice($pathArray,$i+1);
            $status = '200';
            $pathStr = implode('/',$pathArray);
            $includeFile = '';
            $denyDirPattern = '/^'.__NBFramePregConcat__($confArray['denyDirArray']).'/';

            if (preg_match($denyDirPattern, $pathStr)) {
                $status = '403';
            } else if (preg_match('/\.\./', $pathStr)) {
                $status = '403';
            } else {
               $path = dirname(__FILE__).'/modules/'.$confArray['dirname'];
                if (file_exists($path . '/index.html')) {
                    $includeFile = $path . '/index.html';
                    $status = '200';
                } else if (file_exists($path . '/index.php')) {
                    $includeFile = $path . '/index.php';
                    $status = '200';
                } else {
                    $includeFile = '';
                    $status = '403';
                }
                for ($i = 0; $i < count($pathArray); $i++) {
                    if ($pathArray[$i]) {
                        $path .= '/'. $pathArray[$i];
                        if (is_dir($path)) {
                            if (file_exists($path . '/index.html')) {
                                $includeFile = $path . '/index.html';
                                $status = '200';
                            } else if (file_exists($path . '/index.php')) {
                                $includeFile = $path . '/index.php';
                                $status = '200';
                            } else {
                                $status = '403';
                            }
                        } else if (file_exists($path)) {
                            $includeFile = $path;
                            $status = '200';
                        } else if (file_exists($path.'.php')) {
                            $includeFile = $path.'.php';
                            $status = '200';
                            break;
                        } else {
                            $includeFile = '';
                            $status = '404';
                        }
                    }
                }
            }
            $GLOBALS['NBFrameURLShotened'] = true;
            $ext = preg_replace('/.*\.([a-zA-Z0-9]+)$/', '\\1', $includeFile);
            return array($includeFile, $status, $ext);
        }

        function __NBFramePregConcat__($strArray) {
            $result = '(';
            $delim = '';
            foreach ($strArray as $str) {
                $result .= $delim.preg_quote($str,'/');
                $delim = '|';
            }
            $result .= ')';
            return $result;
        }
    }

    list($_NBFrontIncludeFile, $_NBFrontStatus, $_NBFrontExt) = __NBFrameShorURLParser__($NBFrameFrontendConf);

    $_SERVER['SCRIPT_FILENAME'] = $_NBFrontIncludeFile;
    $_SERVER['SCRIPT_NAME'] = basename($_NBFrontIncludeFile);
    $_SERVER['PATH_TRANSLATED'] = $_NBFrontIncludeFile;

    $_NBFrontWrapDirPattern = '/^'.preg_quote(dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].'/','/');
    $_NBFrontWrapDirPattern .= __NBFramePregConcat__($NBFrameFrontendConf['wrapDirArray']);
    $_NBFrontWrapDirPattern .= '.*'. __NBFramePregConcat__($NBFrameFrontendConf['wrapExtArray']).'$/';

    if ($_NBFrontIncludeFile && $_NBFrontStatus == '200') {
        if (preg_match($_NBFrontWrapDirPattern, $_NBFrontIncludeFile)) {
            // Wrap a specified HTML file with XOOPS Theme
            chdir(dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname']);
            require_once '../../mainfile.php';
            require_once XOOPS_ROOT_PATH.'/header.php';
            require_once $_NBFrontIncludeFile;
            require_once XOOPS_ROOT_PATH.'/footer.php';
        } else if ($_NBFrontExt == 'php') {
            chdir(dirname($_NBFrontIncludeFile));
            require_once $_NBFrontIncludeFile;
            exit();
        } else if (array_key_exists($_NBFrontExt, $NBFrameFrontendConf['mimeArray'])) {
            NBFrameHTTPOutputSubset::putFile($_NBFrontIncludeFile, $NBFrameFrontendConf['mimeArray'][$_NBFrontExt]);
        } else {
            header('Location: '.$_SERVER['REQUEST_URI']);
        }
    } else if ($_NBFrontStatus == '403') {
        header('HTTP/1.0 403 Forbidden');
        exit('403 Forbidden');
    } else if ($_NBFrontStatus == '404') {
        if (!empty($NBFrameFrontendConf['failOver']) 
            && file_exists(dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].$NBFrameFrontendConf['failOver'])) {
            $_NBFrontIncludeFile = dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].$NBFrameFrontendConf['failOver'];
            chdir(dirname($_NBFrontIncludeFile));
            include $_NBFrontIncludeFile;
            exit();
        } else if (file_exists(dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].'/include/NBFrameLoader.inc.php')) {
            // If NBFrame based Module, try for executing module front controller (index.php)
            $_NBFrontIncludeFile = dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].'/index.php';
            chdir(dirname($_NBFrontIncludeFile));
            include $_NBFrontIncludeFile;
            exit();
        } else {
            header('HTTP/1.0 404 Not Found');
            exit('404 Not Found');
        }
    }
} else { //Not for Frontend execution (definition of check function whether this file is ShortURL frontend)
    if (!function_exists('NBFrameShortURL_sig_'.$NBFrameFrontendConf['thisname'])) {
        $sig_func = 'function NBFrameShortURL_sig_'.$NBFrameFrontendConf['thisname'].'() { return "'.$NBFrameFrontendConf['dirname'].'";}';
        eval($sig_func);
    }
}
?>
