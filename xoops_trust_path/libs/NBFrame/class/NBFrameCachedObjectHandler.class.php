<?php
/**
 *
 * @package NBFrame
 * @version $Id: admin.php,v 1.2 2007/06/24 07:26:21 nobunobu Exp $
 * @copyright Copyright 2007 NobuNobuXOOPS Project <http://sourceforge.net/projects/nobunobuxoops/>
 * @author NobuNobu <nobunobu@nobunobu.com>
 * @license http://www.gnu.org/licenses/gpl.txt GNU GENERAL PUBLIC LICENSE Version 2
 *
 */
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameCachedObjectHandler')) {
    NBFrame::using('ObjectHandler');

    class NBFrameCachedObjectHandler  extends NBFrameObjectHandler
    {
        var $mUseFullCache;
        var $mCacheLimit;
        var $mFullCached;

        function NBFrameCachedObjectHandler($db)  {
            parent::NBFrameObjectHandler($db);
            $this->mUseFullCache = true;
            $this->mCacheLimit = 0;
            $this->mFullCached = false;
        }

        /**
         * �쥳���ɤμ���(�ץ饤�ޥ꡼�����ˤ���ո�����
         *
         * @param   mixed $key ��������
         *
         * @return  object  {@link NBFrameObject}, FALSE on fail
         */
        function &get($keys) {
            $ret = false;
            if ($cacheKey = $this->_getCacheKey($keys)) {
                if ($GLOBALS['_NBFrameTableCache']->exists($this->mTableName, $cacheKey)) {
                    $record->assignVars($GLOBALS['_NBFrameTableCache']->get($this->mTableName, $cacheKey));
                    $ret = $record;
                } else {
                    $ret = parent::get($keys);
                    $GLOBALS['_NBFrameTableCache']->set($this->tableName, $cacheKey, $ret, $this->cacheLimit);
                }
            }
            return $ret;
        }

        function _getCacheKey($keys) {
            $record =& $this->create(false);
            $recordKeys = $record->getKeyFields();
            $cacheKey = array();
            if (!is_array($keys)) {
                if (count($recordKeys) == 1) {
                    $keys = array($recordKeys[0] => $keys);
                } else {
                    return false;
                }
            }
            foreach ($recordKeys as $key) {
                if (array_key_exists($key, $keys)) {
                    $cacheKey[$key] = $record->get($key);
                } else {
                    unset($record);
                    return false;
                }
            }
            unset($record);
            return serialize($cacheKey);
        }

        /**
         * �쥳���ɤ���¸
         *
         * @param   object  &$record    {@link NBFrameObject} object
         * @param   bool    $force      POST�᥽�åɰʳ��Ƕ��������������ture
         *
         * @return  bool    �����λ��� TRUE
         */
        function insert(&$record,$force=false,$updateOnlyChanged=false)
        {
            $GLOBALS['_NBFrameTableCache']->reset($this->mTableName, $record->cacheKey());
            $this->mFullCached = false;
            return parent::insert($record,$force,$updateOnlyChanged);
        }

        /**
         * �쥳���ɤκ��
         *
         * @param   object  &$record  {@link NBFrameObject} object
         * @param   bool    $force      POST�᥽�åɰʳ��Ƕ��������������ture
         *
         * @return  bool    �����λ��� TRUE
         */
        function delete(&$record,$force=false)
        {
            $GLOBALS['_NBFrameTableCache']->reset($this->mTableName, $record->cacheKey());
            return parent::delete($record,$force);
        }

        /**
         * �ơ��֥�ξ�︡���ˤ��ʣ���쥳���ɼ���
         *
         * @param   object  $criteria   {@link NBFrameObject} �������
         * @param   bool $id_as_key     �ץ饤�ޥ꡼�������������Υ����ˤ������true
         *
         * @return  mixed Array         ������̥쥳���ɤ�����
         */
        function &getObjects($criteria = null, $id_as_key = false, $fieldlist="", $distinct = false, $joindef = false)
        {
            $records = array();
            //���ΤȤ���ϡ����˸��ꤵ�줿���Ǥ�������å����Ȥ��ʤ�
            if (($this->mUseFullCache) && ($this->mFullCached) && (empty($criteria))&& (!$fieldlist) && (!$distinct) && (!$joindef)) {
                foreach ($GLOBALS['_NBFrameTableCache']->getFull($this->mTableName) as $myrow) {
                    $record =& $this->create(false);
                    $record->assignVars($myrow);
                    if (!$id_as_key) {
                        $records[] =& $record;
                    } else {
                        $ids = $record->getKeyFields();
                        $r =& $records;
                        $count_ids = count($ids);
                        for ($i=0; $i<$count_ids; $i++) {
                            if ($i == $count_ids-1) {
                                $r[$myrow[$ids[$i]]] =& $record;
                            } else {
                                $r[$myrow[$ids[$i]]] = array();
                                $r =& $r[$myrow[$ids[$i]]];
                            }
                        }
                    }
                    unset($record);
                }
                return $records;
            }

            if ($result =& $this->open($criteria, $fieldlist, $distinct, $joindef)) {
                if (($this->mUseFullCache) && (empty($criteria)) && (!$fieldlist) && (!$distinct) && (!$joindef)) {
                    $this->mFullCached = true;
                }
                while ($myrow = $this->db->fetchArray($result)) {
                    $record =& $this->create(false);
                    $record->assignVars($myrow);
                    if (!$id_as_key) {
                        $records[] =& $record;
                    } else {
                        $ids = $record->getKeyFields();
                        $r =& $records;
                        $count_ids = count($ids);
                        for ($i=0; $i<$count_ids; $i++) {
                            if ($i == $count_ids-1) {
                                $r[$myrow[$ids[$i]]] =& $record;
                            } else {
                                if (!isset($r[$myrow[$ids[$i]]])) {
                                    $r[$myrow[$ids[$i]]] = array();
                                }
                                $r =& $r[$myrow[$ids[$i]]];
                            }
                        }
                    }
                    if (!$fieldlist) {
                        $GLOBALS['_NBFrameTableCache']->set($this->mTableName, $record->cacheKey(), $myrow, $this->mCacheLimit);
                    }
                    unset($record);
                }
                $this->db->freeRecordSet($result);
            }
            return $records;
        }

        function &getNext(&$resultSet, $setCache=true)
        {
            if ($myrow = $this->db->fetchArray($resultSet)) {
                $record =& $this->create(false);
                $record->assignVars($myrow);
                if ($setCache) {
                    $GLOBALS['_NBFrameTableCache']->set($this->mTableName, $record->cacheKey(), $myrow, $this->mCacheLimit);                }
                return $record;
            } else {
                $result = false;
                return $result;
            }
        }

        /**
         * �ơ��֥�ξ�︡���ˤ��ʣ���쥳���ɰ�繹��(�оݥե�����ɤϰ�ĤΤ�)
         *
         * @param   string  $fieldname  �����ե������̾
         * @param   mixed   $fieldvalue ������
         * @param   object  $criteria   {@link NBFrameObject} �������
         * @param   bool    $force      POST�᥽�åɰʳ��Ƕ��������������ture
         *
         * @return  mixed Array         ������̥쥳���ɤ�����
         */
        function updateAll($fieldname, $fieldvalue, $criteria = null, $force=false)
        {
            $GLOBALS['_NBFrameTableCache']->clear($this->mTableName);
            $this->mFullCached = false;
            return parent::updateAll($fieldname, $fieldvalue, $criteria, $force);
        }

        /**
         * �ơ��֥�ξ�︡���ˤ��ʣ���쥳���ɺ��
         *
         * @param   object  $criteria   {@link NBFrameObject} �������
         * @param   bool    $force      POST�᥽�åɰʳ��Ƕ��������������ture
         *
         * @return  bool    �����λ��� TRUE
         */
        function deleteAll($criteria = null, $force=false)
        {
            $GLOBALS['_NBFrameTableCache']->clear($this->mTableName);
            $this->mFullCached = false;
            return parent::deleteAll($criteria, $force);
        }
    }
}
?>
