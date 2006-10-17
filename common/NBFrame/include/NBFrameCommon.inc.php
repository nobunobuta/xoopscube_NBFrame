<?php
if(!defined('NBFRAME_COMMON_FUNCTION_INCLUDED')){
    define('NBFRAME_COMMON_FUNCTION_INCLUDED', 1) ;

    function NBFrameGetModuleConfig($dirname, $conf_name) {
        if (empty($GLOBALS['NBFrameModuleConfig'][$dirname])) {
            $module_handler =& xoops_gethandler('module');
            $module =& $module_handler->getByDirname($dirname);
            $mid = $module->getVar('mid');
            
            $config_handler =& xoops_gethandler('config');
            $GLOBALS['NBFrameModuleConfig'][$dirname] =& $config_handler->getConfigList($mid);
        }
        $value = $GLOBALS['NBFrameModuleConfig'][$dirname][$conf_name];
        return ($value);
    }

    function NBFrameSetModuleConfig($dirname, $conf_name, $conf_value) {
        $module_handler =& xoops_gethandler('module');
        $module =& $module_handler->getByDirname($dirname);
        $mid = $module->getVar('mid');
        $config_handler =& xoops_gethandler('configitem');
        $criteria = new CriteriaCompo(new Criteria('conf_modid', $mid));
        $criteria->add(new Criteria('conf_name', $conf_name));
        $configitems =& $config_handler->getObjects($criteria, false);
        if (count($configitems)==1) {
            $configitems[0]->setVar('conf_value', $conf_value);
            $config_handler->insert($configitems[0]);
        }
        if (isset($GLOBALS['NBFrameModuleConfig'][$dirname][$conf_name])) {
            $GLOBALS['NBFrameModuleConfig'][$dirname][$conf_name] = $conf_value;
        }
    }

    function NBFrameCheckRight($gperm_name, $gperm_itemid) {
        if (is_object($GLOBALS['xoopsUser'])) {
            $groups = $GLOBALS['xoopsUser']->getGroups();
        } else {
            $groups = array(XOOPS_GROUP_ANONYMOUS);
        }
        $gpermHandler = xoops_gethandler('groupperm');
        return $gpermHandler->checkRight($gperm_name, $gperm_itemid, 
                                    $groups, $GLOBALS['xoopsModule']->getVar('mid'));
    }
}
?>
