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
// Only you need is rename this file to your module name and put it into XOOPS_ROOT_PATH.
//
// You can use this file under Apache HTTP Server environment, with 'AcceptPathInfo On' Directive setting
// and 'Options +MultiViews'.
//
// If you can't set 'AcceptPathInfo On' in you Apache environment, you can not use this file.
//
// Even if you could not specify 'Options +MultiViews', you can use another shortend URL like
//   http://foo/modules/simple/index.php => http://foo/simple.php/index.php
// (But,with most of Apache Server environments, you can set this option via .htaccess file at XOOPS_ROOT_PATH)
//
// I did not test so much, so it is a just a sample of my idea.

//*******************************************************
// Configuration (Maybe, you may not edit this for trial)
//*******************************************************

// You can set Dirname directly like following line
//  $NBFrameFrontendConf['dirname']='simple';
$NBFrameFrontendConf['dirname'] = preg_replace('/(.*)(\.php)/','\\1', basename(__FILE__));

// Following module Directory Should not Access via HTTP request.
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

//Follwing extention file will send out via NBFrameHTTPOutput Class
// (Accessing to other extension file cause HTTP redirect);
$NBFrameFrontendConf['mimeArray'] = array(
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'png' => 'image/png',
    'swf' => 'application/x-shockwave-flash',
    'html' =>'text/html',
    'js'   =>'application/x-javascript',
    'css'  =>'text/css',
);

//If you are in Apache MultiViews environment, you can set this to true
//to avoid HTTP access to this file with php file extension.
$NBFrameFrontendConf['disallowWithPhpExt'] = false;

// Class definition of NBFrameHTTPOutputSubset
// This class delived from NBFrame Class Libraries
// But it's independent form any other NBFrame Classes
if (!class_exists('NBFrameHTTPOutputSubset')) {
    class NBFrameHTTPOutputSubset {
        function putFile($fileName, $contentType) {
            error_reporting(E_ERROR);
            if (file_exists($fileName)) {
                header('Content-Type: '.$contentType);
                header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                NBFrameHTTPOutputSubset::staticContentHeader(filemtime($fileName), $fileName);
                $handle = fopen($fileName,'rb');
                $content = '';
                while (!feof($handle)) {
                      $content .= fread($handle, 16384);
                }
                header('Content-Length: '.strlen($content));
                echo $content;
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

if (!function_exists('__NBFrameShortURLFrontEnd__')) {
    function __NBFrameShorURLParser__($confArray) {

        $denyDirPattern = '/^(';
        $delim = '';
        foreach ($confArray['denyDirArray'] as $denyDir) {
            $denyDirPattern .= $delim.preg_quote($denyDir,'/');
            $delim = '|';
        }
        $denyDirPattern .= ')/';

        if (isset($_SERVER['REQUEST_URI'])) {
            if ($confArray['disallowWithPhpExt'] && preg_match('/^'.preg_quote('/'.basename(__FILE__),'/').'/',$_SERVER['REQUEST_URI'])) {
                header('HTTP/1.0 404 Not Found');
                exit('404 Not Found');
            }
            // Force redirect URL eg. http://for/simple to http://for/simple/
            if (!preg_match('/^(.*\/)?'.preg_quote('/'.$confArray['dirname'],'/').'(\.php)?\//',$_SERVER['REQUEST_URI'])) {
                $uri = preg_replace('/^(.*\/)?('.preg_quote('/'.$confArray['dirname'],'/').')(\.php)?/','\\1\\2\\3/', $_SERVER['REQUEST_URI']);
                header('Location:'. $uri);
            }
            
            // Rewrite Some Server Variables.
            $origRequestUri = $_SERVER['REQUEST_URI'];
            list ($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'], $_SERVER['HTTP_REFERER']) =
                preg_replace('/^(.*\/)?'.preg_quote('/'.$confArray['dirname'],'/').'(\.php)?/','\\1/modules/'.$confArray['dirname'], 
                             array($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'], $_SERVER['HTTP_REFERER']));

        }
        
        $queryArray = preg_split('/[?#]/', $origRequestUri);
        $pathArray = array_slice(explode('/', $queryArray[0]),1);
        $i = 0;
        foreach($pathArray as $path) {
            if ($path != $confArray['dirname']) {
                $i++;
            } else {
                break;
            }
        }
        $status = '200';
        $includeFile = '';
        $pathArray = array_slice($pathArray,$i+1);
        $pathStr = implode('/',$pathArray);
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
}

list($_NBFrontIncludeFile, $_NBFrontStatus, $_NBFrontExt) = __NBFrameShorURLParser__($NBFrameFrontendConf);

if ($_NBFrontIncludeFile && $_NBFrontStatus == '200') {
    if ($_NBFrontExt == 'php') {
        $_SERVER['SCRIPT_FILENAME'] = $_NBFrontIncludeFile;
        $_SERVER['SCRIPT_NAME'] = basename($_NBFrontIncludeFile);
        chdir(dirname($_NBFrontIncludeFile));
        include $_NBFrontIncludeFile;
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
    if (file_exists(dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].'/include/NBFrameLoader.inc.php')) {
        // If NBFrame based Module, try for executing module front controller (index.php)
        $_NBFrontIncludeFile = dirname(__FILE__).'/modules/'.$NBFrameFrontendConf['dirname'].'/index.php';
        $_SERVER['SCRIPT_FILENAME'] = $_NBFrontIncludeFile;
        $_SERVER['SCRIPT_NAME'] = basename($_NBFrontIncludeFile);
        include $_NBFrontIncludeFile;
        exit();
    } else {
        header('HTTP/1.0 404 Not Found');
        exit('404 Not Found');
    }
}
?>
