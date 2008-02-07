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
if (!class_exists('NBFrameGetImageAction')) {
    NBFrame::using('Action');
    class NBFrameGetImageAction extends NBFrameAction {
        var $mLoadCommon = false;
        function executeDefaultOp() {
            error_reporting(E_ERROR);
            if (isset($_GET['NBImgFile'])) {
                $fileBaseName = basename($_GET['NBImgFile']);
            } else if (isset($_GET['file'])) {
                $fileBaseName = basename($_GET['file']);
            } else {
                return;
            }
            $fileName = NBFrame::findFile($fileBaseName, $this->mEnvironment, 'images');
            if (!empty($fileName) && preg_match('/\.(jpeg|jpg|gif|png|swf)$/', strtolower($fileBaseName), $match)) {
                $fileExt = $match[1];
                if ($fileExt =='jpeg' || $fileExt =='jpg') {
                    $mimeType = 'image/jpeg';
                } else if ($fileExt =='gif'){
                    $mimeType = 'image/gif';
                } else if ($fileExt =='png'){
                    $mimeType = 'image/png';
                } else if ($fileExt =='swf'){
                    $mimeType = 'application/x-shockwave-flash';
                }
                NBFrame::using('HTTPOutput');
                NBFrameHTTPOutput::putFile($fileName, $mimeType);
            } else {
                NBFrame::display404Page();
            }
        }
        
        function getParamString(&$environment, $paramArray) {  
            if (isset($paramArray['NBImgFile'])) {
                return 'images/'.rawurlencode($paramArray['NBImgFile']);
            }
        }
    }
}
?>
