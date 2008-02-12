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
if (!class_exists('NBFrameRedirectAction')) {
    NBFrame::using('Action');
    class NBFrameRedirectAction extends NBFrameAction {
        function executeDefaultOp() {
            if (isset($_REQUEST['NBFrameNextAction'])) {
                $action=basename($_REQUEST['NBFrameNextAction']);
            } else {
                $action='';
            }
            if (isset($_GET['NBFrameNextOp'])) {
                $op=basename($_GET['NBFrameNextOp']);
            } else {
                $op='';
            }
            $paramArray = array();
            foreach($_GET as $key=>$param) {
                if(($key!='action') && !preg_match('/^NBFrame/', $key)) {
                    $paramArray[$key]=$param;
                }
            }
            header('Location: '.$this->mEnvironment->getActionUrl($action, $paramArray, '',false, false));
            exit();
        }
    }
}
