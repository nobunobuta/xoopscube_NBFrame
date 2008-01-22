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
if(!class_exists('NBFrameConfigHandler')) {
/*
 * Config type
 */
define('XOOPS_NB_CONF', 1);
define('XOOPS_NB_CONF_USER', 2);
define('XOOPS_NB_CONF_METAFOOTER', 3);
define('XOOPS_NB_CONF_CENSOR', 4);
define('XOOPS_NB_CONF_SEARCH', 5);
define('XOOPS_NB_CONF_MAILER', 6);


    class NBFrameConfigHandler extends NBFrameObjectHandler {
        var $mTableName = 'config';
        var $mUseModuleTablePrefix = false;
        var $_moduleConfigCache = array();
        
        function getModuleConfig($dirname, $conf_name) {
            if (empty($this->_moduleConfigCache[$dirname])) {
                $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
                $moduleObject =& $moduleHandler->getByDirname($dirname);
                $mid = $moduleObject->getVar('mid');
                $criteria = new CriteriaCompo(new Criteria('conf_modid', $mid));
                $configObjects = $this->getObjects($criteria);
                $config = array();
                foreach($configObjects as $configObject) {
                    $config[$configObject->getVar('conf_name')] = $configObject->getVar('conf_value');
                }
                $this->_moduleConfigCache[$dirname] = $config;
            }
            $value = $this->_moduleConfigCache[$dirname][$conf_name];
            return ($value);
        }

        function setModuleConfig($dirname, $conf_name, $conf_value) {
            $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
            $moduleObject =& $moduleHandler->getByDirname($dirname);
            $mid = $moduleObject->getVar('mid');
            $criteria = new CriteriaCompo(new Criteria('conf_modid', $mid));
            $criteria->add(new Criteria('conf_name', $conf_name));
            $configObjects = $this->getObjects($criteria, false);
            if (count($configObjects)==1) {
                $configObjects[0]->setVar('conf_value', $conf_value);
                $this->insert($configObjects[0]);
            }
            if (empty($this->_moduleConfigCache[$dirname])) {
                $this->_moduleConfigCache[$dirname][$conf_name] = $conf_value;
            }
            return ($value);
        }
    }
}
?>
