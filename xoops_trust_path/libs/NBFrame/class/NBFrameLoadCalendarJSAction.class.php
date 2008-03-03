<?php
/**
 *
 * @package NBFrame
 * @version $Id: MyGmapLoadJsciptAction.class.php 1335 2008-02-18 08:49:11Z nobunobu $
 * @copyright Copyright 2006-2008 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrameLoadCalendarJSAction')) {
    NBFrame::using('Action');
    class NBFrameLoadCalendarJSAction extends NBFrameAction {
        function prepare() {
            $this->mAllowedOp =array('default','main');
            $this->mRequest->defParam('lang', 'GET', 'var', 'english');
        }
        function executeDefaultOp() {
            error_reporting(E_ERROR);
            $lang = basename($this->mRequest->getParam('lang'));
            header('Content-type: application/x-javascript;charset="EUC-JP"');
            $url = $this->mEnvironment->getActionUrl('NBFrame.LoadCalendarJS',array('op'=>'main', 'lang'=>basename($lang)));
            include NBFRAME_BASE_DIR.'/templates/NBFrameCalendarJSLoader.tpl.php';
            exit();
        }
        function executeMainOp() {
            error_reporting(E_ERROR);
            $lang = basename($this->mRequest->getParam('lang'));
            include_once XOOPS_ROOT_PATH.'/language/'.$lang.'/calendar.php';
            header('Content-type: application/x-javascript;charset="EUC-JP"');
            include NBFRAME_BASE_DIR.'/templates/NBFrameCalendarJS.tpl.php';
            exit();
        }
    }
}
