<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameHTTPOutput')) {
    class NBFrameHTTPOutput {
        function putFile($fileName, $contentType, $static=true) {
            error_reporting(E_ERROR);
            if (file_exists($fileName)) {
                header('Content-Type: '.$contentType);
                if ($static) {
                    header('Content-Disposition: inline; filename="'.basename($fileName).'"');
                    NBFrameHTTPOutput::staticContentHeader(filemtime($fileName), $fileName);
                } else {
                    header('Pragma: no-cache');
                    header('Cache-Control: no-store, no-cache, must-revalidate,post-check=0, pre-check=0');
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
//                header('Cache-Control: max-age=0');
//                header('Expires:'.gmdate('D, d M Y H:i:s', time()+60).' GMT');
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
