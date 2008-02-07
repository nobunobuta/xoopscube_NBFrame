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

if (!class_exists('NBFrameHTTPOutput')) {
    class NBFrameHTTPOutput {
        function putFile($fileName, $contentType, $static=true, $expires=-1, $do_exit=true) {
            error_reporting(E_ERROR);
            if (file_exists($fileName)) {
                header('Content-Type: '.$contentType);
                if ($static) {
                    header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                    NBFrameHTTPOutput::staticContentHeader(filemtime($fileName), $fileName, $expires);
                } else {
                    header('Pragma: no-cache');
                    header('Cache-Control: private, no-store, no-cache, must-revalidate,post-check=0, pre-check=0');
                    header('Expires: '.gmdate('D, d M Y H:i:s', time()-60).' GMT');
                    header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                }
                $handle = fopen($fileName,'rb');
                $content = '';
                while (!feof($handle)) {
                      $content .= fread($handle, 16384);
                }
                header('Content-Length: '.strlen($content));
                echo $content;
                if ($do_exit) exit();
            }
        }
        
        function staticContentHeader($mod_timestamp, $etag_base='', $expires=-1) {
            if (!empty($mod_timestamp)) {
                $etag = md5($_SERVER["REQUEST_URI"] . $mod_timestamp . $etag_base);
                header('Pragma:');
                header('Etag: "'.$etag.'"' );
                header('Cache-Control:');
                if ($expires == -1) {
                    header('Expires:');
                } else {
                    header('Expires: '.gmdate('D, d M Y H:i:s', time()+intval($expires)).' GMT');
                }
                header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mod_timestamp).' GMT');
                if((!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && ($mod_timestamp==NBFrameHTTPOutput::_str2Time($_SERVER['HTTP_IF_MODIFIED_SINCE'])))||
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
$dirname = preg_replace('/(.*)(\.php)/','\\1', basename(__FILE__));
$denyDirArray = array(
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
    'private/',
    'tempaltes/',
);
$denyDirPattern = '/^(';
$delim = '';
foreach ($denyDirArray as $denyDir) {
    $denyDirPattern .= $delim.preg_quote($denyDir,'/');
    $delim = '|';
}
$denyDirPattern .= ')/';

if (isset($_SERVER['REQUEST_URI'])) {
//    if (preg_match('/^'.preg_quote('/'.basename(__FILE__),'/').'/',$_SERVER['REQUEST_URI'])) {
//        header('HTTP/1.0 404 Not Found');
//        exit('404 Not Found');
//    }
    if (!preg_match('/^(.*\/)?'.preg_quote('/'.$dirname,'/').'(\.php)?\//',$_SERVER['REQUEST_URI'])) {
        $uri = preg_replace('/^(.*\/)?('.preg_quote('/'.$dirname,'/').')(\.php)?/','\\1\\2\\3/', $_SERVER['REQUEST_URI']);
        header('Location:'. $uri);
    }
    
    $origRequestUri = $_SERVER['REQUEST_URI'];
    list ($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'], $_SERVER['HTTP_REFERER']) =
        preg_replace('/^(.*\/)?'.preg_quote('/'.$dirname,'/').'(\.php)?/','\\1/modules/'.$dirname, 
                     array($_SERVER['REQUEST_URI'], $_SERVER['PHP_SELF'], $_SERVER['HTTP_REFERER']));

}
$queryArray = explode('?', $origRequestUri,2);
$pathArray = array_slice(explode('/', $queryArray[0]),1);
$i = 0;
foreach($pathArray as $path) {
    if ($path != $dirname) {
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
   $path = dirname(__FILE__).'/modules/'.$dirname;
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

$mimeArray = array(
    'jpeg' => 'image/jpeg',
    'jpg' => 'image/jpeg',
    'gif' => 'image/gif',
    'png' => 'image/png',
    'swf' => 'application/x-shockwave-flash',
    'html' =>'text/html',
    'js'   =>'application/x-javascript',
    'css'  =>'text/css',
);

if ($includeFile && $status == '200') {
    $ext = preg_replace('/.*\.([a-zA-Z0-9]+)$/', '\\1', $includeFile);
    if ($ext == 'php') {
        chdir(dirname($includeFile));
        $_SERVER['SCRIPT_FILENAME'] = $includeFile;
        $_SERVER['SCRIPT_NAME'] = basename($includeFile);
        include $includeFile;
        exit();
    } else if (array_key_exists($ext, $mimeArray)) {
        NBFrameHTTPOutput::putFile($includeFile, $mimeArray[$ext]);
    } else {
        header('Location: '.$_SERVER['REQUEST_URI']);
    }
} else if ($status == '403') {
    header('HTTP/1.0 403 Forbidden');
    exit('403 Forbidden');
} else if ($status == '404') {
    if (file_exists(dirname(__FILE__).'/modules/'.$dirname.'/include/NBFrameLoader.inc.php')) {
        $includeFile = dirname(__FILE__).'/modules/'.$dirname.'/index.php';
        $_SERVER['SCRIPT_FILENAME'] = $includeFile;
        $_SERVER['SCRIPT_NAME'] = basename($includeFile);
        include $includeFile;
        exit();
    } else {
        header('HTTP/1.0 404 Not Found');
        exit('404 Not Found');
    }
}

?>
