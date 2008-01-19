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
if(!class_exists('NBFrameGroupPermHandler')) {
    class NBFrameGroupPermHandler extends NBFrameObjectHandler {
        var $mTableName = 'group_permission';
        var $mUseModuleTablePrefix = false;

        /**
         * Check permission
         * 
         * @param   string    $gpermName       Name of permission
         * @param   int       $gpermItemId     ID of an item
         * @param   int/array $gpermGroupId    A group ID or an array of group IDs
         * @param   int       $gpermModuleId      ID of a module
         * @param   bool      $bypassAdminCheck Do not XOOPS_GROUP_ADMIN check if true.
         * 
         * @return  bool    TRUE if permission is enabled
         */
        function checkRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId, $bypassAdminCheck = false)
        {
            if (($bypassAdminCheck == false) &&
                ((is_array($gpermGroupId) && in_array(XOOPS_GROUP_ADMIN, $gpermGroupId))||
                (XOOPS_GROUP_ADMIN == $gpermGroupId))) {
                return true;
            }

            $criteria =& $this->getCriteria($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId);
            if ($this->getCount($criteria) > 0) {
                return true;
            }
            return false;
        }

        function checkRightByObjectKey($gpermName, &$gpermObject, $gpermGroupId=0)
        {
            if (is_object($gpermObject->mHandler)) {
                $environment =& $gpermObject->mHandler->mEnvironment;
            } else {
                $environment = null;
            }
            if ($gpermGroupId == 0) {
                if (is_object($GLOBALS['xoopsUser'])) {
                    $gpermGroupId = $GLOBALS['xoopsUser']->getGroups();
                } else {
                    $gpermGroupId = XOOPS_GROUP_ANONYMOUS;
                }
            }
            if (!empty($environment)) {
                $moduleObject =& $environment->getModule();
            } else {
                $moduleObject =& $GLOBALS['xoopsModule'];
            }
            $moduleID = $moduleObject->get('mid');
            return $this->checkRight($gpermName, $gpermObject->getKey(), $gpermGroupId, $moduleID, true);
        }

        /**
         * Set a permission
         * 
         * @param   string  $gpermName       Name of permission
         * @param   int     $gpermItemId     ID of an item
         * @param   int     $gpermGroupIdArray   ID of a group or Array
         * @param   int     $gpermModuleId      ID of a module
         *
         * @return  bool    TRUE if success
         */
        function setRight($gpermName, $gpermItemId, $gpermGroupIdArray, $gpermModuleId)
        {
            if (!is_array($gpermGroupIdArray)) {
                $gpermGroupIdArray = array(intval($gpermGroupIdArray));
            }
            $groupIdArray = $this->getGroupIds($gpermName, $gpermItemId, $gpermModuleId);
            foreach($groupIdArray as $group) {
                if (!in_array($group,  $gpermGroupIdArray)) {
                    $this->removeRight($gpermName, $gpermItemId, $group, $gpermModuleId);
                }
            }
            foreach ($gpermGroupIdArray as $gpermGroupId) {
                $this->addRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId);
            }
        }

        function setRightByObjectKey($gpermName, &$gpermObject, $gpermGroupIds)
        {
            if (is_object($gpermObject->mHandler)) {
                $environment =& $gpermObject->mHandler->mEnvironment;
            } else {
                $environment = null;
            }
            if (!empty($environment)) {
                $moduleHandler =& NBFrame::getHandler('NBFrame.xoops.Module', NBFrame::null());
                $moduleObject =& $environment->getModule();
            } else {
                $moduleObject =& $GLOBALS['xoopsModule'];
            }
            $moduleID = $moduleObject->get('mid');
            $this->setRight($gpermName, $gpermObject->getKey(), $gpermGroupIds, $moduleID);
        }

        /**
         * Add a permission
         * 
         * @param   string  $gpermName       Name of permission
         * @param   int     $gpermItemId     ID of an item
         * @param   int     $gpermGroupId    ID of a group
         * @param   int     $gpermModuleId      ID of a module
         *
         * @return  bool    TRUE if success
         */
        function addRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId)
        {
            $criteria =& $this->getCriteria($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId);
            $count = $this->getCount($criteria);
            if ($count == 1) {
                return true;    // Only one record already exist. do nothing.
            } else if ($count > 1) {
                // This case occurs when group_permission table exists from older versions of XOOPS.
                // So, once clear all and insert new record.
                $this->removeRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId);
            }

            $groupPermObject =& $this->create();
            $groupPermObject->set('gperm_name', $gpermName);
            $groupPermObject->set('gperm_groupid', $gpermGroupId);
            $groupPermObject->set('gperm_itemid', $gpermItemId);
            $groupPermObject->set('gperm_modid', $gpermModuleId);
            return $this->insert($groupPermObject);
        }
        
        /**
         * Remove a permission
         * 
         * @param   string  $gpermName       Name of permission
         * @param   int     $gpermItemId     ID of an item
         * @param   int     $gpermGroupId    ID of a group
         * @param   int     $gpermModuleId      ID of a module
         *
         * @return  bool    TRUE jf success
         */
        function removeRight($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId)
        {
            $criteria =& $this->getCriteria($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId);
            return $this->deleteAll($criteria);
        }

        /**
         * Generate a criteria from given params
         * 
         * @param   string  $gpermName       Name of permission
         * @param   int     $gpermItemId     ID of an item
         * @param   int     $gpermGroupId    ID of a group
         * @param   int     $gpermModuleId      ID of a module
         *
         * @return  CriteiaCompo
         */
        function &getCriteria($gpermName, $gpermItemId, $gpermGroupId, $gpermModuleId)
        {
            $criteria = new CriteriaCompo(new Criteria('gperm_modid', intval($gpermModuleId)));
            $criteria->add(new Criteria('gperm_name', $gpermName));
            $gpermItemId = intval($gpermItemId);
            if ($gpermItemId > 0) {
                $criteria->add(new Criteria('gperm_itemid', $gpermItemId));
            }
            if (is_array($gpermGroupId)) {
                if (count($gpermGroupId) > 0) {
                    $criteria2 = new CriteriaCompo();
                    foreach ($gpermGroupId as $gid) {
                        $criteria2->add(new Criteria('gperm_groupid', intval($gid)), 'OR');
                    }
                    $criteria->add($criteria2);
                }
            } else if (intval($gpermGroupId) > 0) {
                $criteria->add(new Criteria('gperm_groupid', intval($gpermGroupId)));
            }
            return $criteria;
        }

        /**
         * Get all item IDs that a group is assigned a specific permission
         * 
         * @param   string    $gpermName       Name of permission
         * @param   int/array $gpermGroupId    A group ID or an array of group IDs
         * @param   int       $gpermModuleId      ID of a module
         *
         * @return  array     array of item IDs
         */
        function getItemIds($gpermName, $gpermGroupId, $gpermModuleId = 1)
        {
            $ret = array();
            $criteria = new CriteriaCompo(new Criteria('gperm_name', $gpermName));
            $criteria->add(new Criteria('gperm_modid', intval($gpermModuleId)));
            if (is_array($gpermGroupId)) {
                $criteria2 = new CriteriaCompo();
                foreach ($gpermGroupId as $gid) {
                    $criteria2->add(new Criteria('gperm_groupid', $gid), 'OR');
                }
                $criteria->add($criteria2);
            } else {
                $criteria->add(new Criteria('gperm_groupid', intval($gpermGroupId)));
            }
            $groupPermObjects =& $this->getObjects($criteria, true);
            foreach (array_keys($groupPermObjects) as $i) {
                $ret[] = $groupPermObjects[$i]->get('gperm_itemid');
            }
            return array_unique($ret);
        }

        /**
         * Get all group IDs assigned a specific permission for a particular item
         * 
         * @param   string  $gpermName       Name of permission
         * @param   int     $gpermItemId     ID of an item
         * @param   int     $gpermModuleId      ID of a module
         *
         * @return  array   array of group IDs
         */
        function getGroupIds($gpermName, $gpermItemId, $gpermModuleId)
        {
            $ret = array();
            $criteria = new CriteriaCompo(new Criteria('gperm_name', $gpermName));
            $criteria->add(new Criteria('gperm_itemid', intval($gpermItemId)));
            $criteria->add(new Criteria('gperm_modid', intval($gpermModuleId)));
            $groupPermObjects =& $this->getObjects($criteria, true);
            foreach (array_keys($groupPermObjects) as $i) {
                $ret[] = $groupPermObjects[$i]->get('gperm_groupid');
            }
            return $ret;
        }
        
        function getGroupIdsByObjectKey($gpermName, &$gpermObject)
        {
            if (is_object($gpermObject->mHandler)) {
                $environment =& $gpermObject->mHandler->mEnvironment;
            } else {
                $environment = null;
            }
            if (!empty($environment)) {
                $moduleObject =& $environment->getModule();
            } else {
                $moduleObject =& $GLOBALS['xoopsModule'];
            }
            $moduleID = $moduleObject->get('mid');
            $result = $this->getGroupIds($gpermName, $gpermObject->getKey(), $moduleID);
            return $result;
        }
    }
}
?>
