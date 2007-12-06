<?php
if (!class_exists('NBFrameGetModuleIconAction')) {
    NBFrame::using('Action');
    class NBFrameGetModuleIconAction extends NBFrameAction {
        var $mLoadCommon = false;
        function executeDefaultOp() {
            error_reporting(E_ERROR);
            if (!empty($_GET['file'])) {
                $fileBaseName = basename($_GET['file']);
                $fileName = NBFrame::findFile($fileBaseName, $this->mEnvironment, 'images');
                if (preg_match('/\.png$/i', $fileBaseName)) $mimeType = 'image/png';
                if (preg_match('/\.gif$/i', $fileBaseName)) $mimeType = 'image/gif';
            } else {
                $fileName = NBFrame::findFile('logo.gif', $this->mEnvironment, 'images');
                if (!empty($fileName)) {
                    $mimeType = 'image/gif';
                } else {
                    $fileName = NBFrame::findFile('logo.png', $this->mEnvironment, 'images');
                    if (!empty($fileName)) {
                        $mimeType = 'image/png';
                    }
                }
            }
            if (!empty($fileName)) {
                $this->putIcon($fileName, $mimeType);
            }
        }

        function putIcon($fileName, $contentType) {
            error_reporting(E_ERROR);
            $dirName = $this->mEnvironment->mDirName;
            $origDirName = $this->mEnvironment->mOrigDirName;
            if (file_exists($fileName)) {
                header('Content-Type: '.$contentType);
                NBFrame::using('HTTPOutput');
                NBFrameHTTPOutput::staticContentHeader(filemtime($fileName), $fileName.$dirName);
                if(($contentType=='image/png') && function_exists( 'imagecreatefrompng' ) && function_exists( 'imagecolorallocate' ) && function_exists( 'imagestring' ) && function_exists( 'imagepng' ) ) {
                    $im = imagecreatefrompng( $fileName ) ;
                    $this->overlayText($im, $dirName, $origDirName);
                    imagepng( $im ) ;
                    imagedestroy( $im ) ;
                } else if(($contentType=='image/gif') && function_exists( 'imagecreatefromgif' ) && function_exists( 'imagecolorallocate' ) && function_exists( 'imagestring' ) && function_exists( 'imagegif' ) ) {
                    $im = imagecreatefromgif($fileName) ;
                    $this->overlayText($im, $dirName, $origDirName);
                    imagegif( $im ) ;
                    imagedestroy( $im ) ;
                } else {
                    $handle = fopen($fileName,'rb');
                    $content = '';
                    while (!feof($handle)) {
                          $content .= fread($handle, 16384);
                    }
                    header('Content-Length: '.strlen($content));
                    echo $content;
                }
                exit();
            }
        }
        
        function overlayText(&$image, $dirname, $origdirname) {
            $gdInfo = gd_info();
            if ($gdInfo['FreeType Support']) {
                $useTTF = true;
                $fonrFileName = NBFRAME_BASE_DIR.'/include/FreeSansBold.ttf';
            } else {
                $useTTF = false;
            }
            if ((imagesx($image) == 92)&&(imagesy($image) == 52)) {//XCube2.0.x Style
                $color = imagecolorallocate( $image , 0 , 0 , 0 ) ; // black
                $px = ( 92 - 6 * strlen( $dirname ) ) / 2 ;
                imagestring( $image , 2 , $px , 34 , $dirname , $color );
            } else if ((imagesx($image) == 127)&&(imagesy($image) == 24)) {//XOOPS 2.1 Style
                $origdirname = ucfirst($origdirname);
                $color_b = imagecolorallocate( $image , 200 , 200 , 200 ) ;
                $color_f1= imagecolorallocate( $image , 0 , 90 , 0 );
                $color_f= imagecolorallocate( $image , 60 , 160 , 60 );
                
                if ($useTTF) {
                    imagettftext ( $image,9, 0, 42, 11,$color_f1,$fonrFileName, $origdirname );
                    imagettftext ( $image,8, 0, 42, 20,$color_f1,$fonrFileName,  " - [".$dirname."]"  );
                } else {
                    imagestring( $image , 2 , 42 , 2 , $origdirname , $color_b ) ;
                    imagestring( $image , 2 , 52 , 13 , "[".$dirname."]" , $color_b ) ;
                    imagestring( $image , 2 , 41 , 1 , $origdirname , $color_f ) ;
                    imagestring( $image , 2 , 51 , 12 , "[".$dirname."]" , $color_f ) ;
                    imagestring( $image , 2 , 40 , 0 , $origdirname , $color_f1) ;
                    imagestring( $image , 2 , 50 , 11 , "[".$dirname."]" , $color_f1) ;
                }
            }
        }
    }
}
?>
