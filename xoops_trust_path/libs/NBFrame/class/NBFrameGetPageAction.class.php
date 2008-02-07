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
if (!class_exists('NBFrameGetPageAction')) {
    NBFrame::using('Action');
    class NBFrameGetPageAction extends NBFrameAction {
        var $mFileName ='';
        function executeDefaultOp() {
            error_reporting(E_ERROR);
            if (isset($_GET['NBContentFile'])) {
                $fileBaseName = basename($_GET['NBContentFile']);
            } else {
                return;
            }
            $fileName = NBFrame::findFile($fileBaseName, $this->mEnvironment, 'pages');
            if (!empty($fileName) && preg_match('/\.(html|htm)$/', strtolower($fileBaseName), $match)) {
                $this->mFileName = $fileName;
            } else {
                NBFrame::display404Page();
            }
            return NBFRAME_ACTION_VIEW_DEFAULT;
        }
        function viewDefaultOp() {
            if ($this->mFileName) {
                $this->mXoopsTpl->display($this->mFileName);
            }
        }

        function getParamString(&$environment, $paramArray) {  
            if (isset($paramArray['NBContentFile'])) {
                return 'contents/'.rawurlencode($paramArray['NBContentFile']);
            }
        }
    }
}
?>
