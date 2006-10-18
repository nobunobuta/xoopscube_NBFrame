<?php
if (!class_exists('NBFrameGetImageAction')) {
    NBFrame::using('Action');
    class NBFrameGetImageAction extends NBFrameAction {
        var $mLoadCommon = false;
        function executeDefaultOp() {
            error_reporting(E_ERROR);
            $fileBaseName = basename($_GET['file']);
            $fileName = NBFrame::findFile($fileBaseName, $this->mEnvironment, 'images');
            if (!empty($fileName) && preg_match('/\.(jpeg|jpg|gif|png)$/', strtolower($fileBaseName), $match)) {
                $fileExt = $match[1];
                if ($fileExt =='jpeg' || $fileExt =='jpg') {
                    $mimeType = 'image/jpeg';
                } else if ($fileExt =='gif'){
                    $mimeType = 'image/gif';
                } else if ($fileExt =='png'){
                    $mimeType = 'image/png';
                }
                NBFrame::using('HTTPOutput');
                NBFrameHTTPOutput::putFile($fileName, $mimeType);
            }
        }
    }
}
?>
