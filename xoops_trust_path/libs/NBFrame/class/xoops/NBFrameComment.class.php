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

if(!class_exists('NBFrameComment.')) {
    class NBFrameComment extends NBFrameObject
    {
        function prepare() {
            $this->setNameField('com_title');
        }

        function isRoot() {
            return ($this->get('com_id') == $this->get('com_rootid'));
        }

        function &createChild() {
            $commentObject=new XoopsComment();
            $commentObject->setNew();
            $commentObject->set('com_pid',$this->get('com_id'));
            $commentObject->set('com_rootid',$this->get('com_rootid'));
            $commentObject->set('com_modid',$this->get('com_modid'));
            $commentObject->set('com_itemid',$this->get('com_itemid'));
            $commentObject->set('com_exparams',$this->get('com_exparams'));

            $title = $this->get('com_title');
            if (preg_match("/^Re:(.+)$/", $title, $matches)) {
                $commentObject->set('com_title', "Re[2]: " . $matches[1]);
            }
            elseif (preg_match("/^Re\[(\d+)\]:(.+)$/", $title, $matches)) {
                $commentObject->set('com_title', "Re[" . ($matches[1] + 1) . "]: " . $matches[2]);
            }

            return $commentObject;
        }
    }

    class NBFrameCommentHandler extends NBFrameObjectHandler {
        var $mTableName = 'xoopscomment';
        var $mUseModuleTablePrefix = false;

        function insert(&$record, $force=false, $updateOnlyChanged=false) {
            if ($record->isNew()) {
                $record->set('com_created', time());
            } else {
                $record->set('com_modified', time());
            }
            return parent::insert($record, $force, $updateOnlyChanged);
        }

        function getByItemId($module_id, $item_id, $order = null, $status = null, $limit = null, $start = 0) {
            $criteria = new CriteriaCompo(new Criteria('com_modid', $module_id));
            $criteria->add(new Criteria('com_itemid', $item_id));
            if (isset($status)) {
                $criteria->add(new Criteria('com_status', $status));
            }
            if (isset($order)) {
                $criteria->setOrder($order);
            }
            if (isset($limit)) {
                $criteria->setLimit($limit);
                $criteria->setStart($start);
            }
            return $this->getObjects($criteria);
        }

        function &getCountByItemId($module_id, $item_id, $status = null) {
            $criteria = new CriteriaCompo(new Criteria('com_modid', $module_id));
            $criteria->add(new Criteria('com_itemid', $item_id));
            if (isset($status)) {
                $criteria->add(new Criteria('com_status', $status));
            }
            return $this->getCount($criteria);
        }

        function &getTopComments($module_id, $item_id, $order, $status = null) {
            $criteria = new CriteriaCompo(new Criteria('com_modid', $module_id));
            $criteria->add(new Criteria('com_itemid', $item_id));
            $criteria->add(new Criteria('com_pid', 0));
            if (isset($status)) {
                $criteria->add(new Criteria('com_status', $status));
            }
            $criteria->setOrder($order);
            $ret =& $this->getObjects($criteria);
            return $ret;
        }

        function &getThread($comment_rootid, $comment_id, $status = null)
        {
            $criteria = new CriteriaCompo(new Criteria('com_rootid', $comment_rootid));
            $criteria->add(new Criteria('com_id', $comment_id, '>='));
            if (isset($status)) {
                $criteria->add(new Criteria('com_status', $status));
            }
            return $this->getObjects($criteria);
        }
    }
}
?>
