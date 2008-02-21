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
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameHTTPOutput')) {
    class NBFrameHTTPOutput {
        function putFile($fileName, $contentType, $static=true, $expires=-1, $do_exit=true, $unit=0) {
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
                if (!empty($unit)) {
                    if (($start+$unit-1) < $end) {
                        $end = $start+$unit-1;
                    }
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
                header('Content-Length: '.$size);
                if ($static) {
                    header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                    NBFrameHTTPOutput::staticContentHeader(filemtime($fileName), $fileName, $expires);
                } else {
                    header('Pragma: no-cache');
                    header('Cache-Control: no-store, no-cache, must-revalidate,post-check=0, pre-check=0');
                    header('Expires: '.gmdate('D, d M Y H:i:s', time()-60).' GMT');
                    header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                }
                while (ob_get_level()) {
                    ob_end_clean();
                }
                ob_implicit_flush(true);

                $handle = fopen($fileName,'rb');
                fseek($handle, $start);
                $pos = 0;
                $block = 16384;
                while ($pos < $size) {
                      if (($pos + $block) > $size) {
                        $block = $size - $pos;
                      }
                      $pos += $block;
                      echo fread($handle, $block);
                      flush();
                }
                fclose($handle);unset($handle);

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
