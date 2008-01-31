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
        var $_moduleIdCache = array();
        
        
        function getConfig($confName, $moduleId=0, $categoryId=null) {
            if ($categoryId == null) {
                if ($moduleId == 0) {
                    $categoryId = XOOPS_NB_CONF;
                } else {
                    $categoryId = 0;
                }
            }
            if (empty($this->_moduleConfigCache[$moduleId][$categoryId])) {
                $criteria = new CriteriaCompo(new Criteria('conf_modid', $moduleId));
                $criteria->add(new Criteria('conf_catid', $categoryId));
                $configObjects = $this->getObjects($criteria);
                $config = array();
                foreach($configObjects as $configObject) {
                    $config[$configObject->getVar('conf_name')] = $configObject->getVar('conf_value');
                }
                $this->_moduleConfigCache[$moduleId][$categoryId] = $config;
            }
            if (isset($this->_moduleConfigCache[$moduleId][$categoryId][$confName])) {
                $value = $this->_moduleConfigCache[$moduleId][$categoryId][$confName];
            } else {
                $value = null;
            }
            return ($value);
        }

        function getModuleConfig($dirName, $confName, $categoryId=0) {
        
            if (empty($this->_moduleIdCache[$dirName])) {
                $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
                $moduleObject =& $moduleHandler->getByDirname($dirName);
                $this->_moduleIdCache[$dirName] = $moduleObject->get('mid');
            }
            $moduleId = $this->_moduleIdCache[$dirName];
            return $this->getConfig($confName, $moduleId, $categoryId);
        }

        function setModuleConfig($dirName, $confName, $confValue, $categoryId=0) {
            $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
            $moduleObject =& $moduleHandler->getByDirname($dirName);
            $moduleId = $moduleObject->getVar('mid');
            $criteria = new CriteriaCompo(new Criteria('conf_modid', $moduleId));
            $criteria->add(new Criteria('conf_catid', $categoryId));
            $criteria->add(new Criteria('conf_name', $confName));
            $configObjects = $this->getObjects($criteria, false);
            if (count($configObjects)==1) {
                $configObjects[0]->setVar('conf_value', $confValue);
                $this->insert($configObjects[0]);
            }
            if (empty($this->_moduleConfigCache[$dirName])) {
                $this->_moduleConfigCache[$moduleId][$categoryId][$confName] = $confValue;
            }
            return ($value);
        }
    }
}
?>
