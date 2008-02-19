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
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameObjectList')) {

    NBFrame::using('Base');
    class NBFrameObjectList extends NBFrameBase {
        var $mAction;
        var $mElements;
        var $mDirName;
        var $mListHeaders;
        var $mListRecords;

        function NBFrameObjectList(&$environment) {
            parent::NBFrameBase($environment);
        }
        
        function prepare() {
        }

        function bindAction(&$action) {
            $this->mAction =& $action;
            $this->mDirName = $action->mDirName;
            if ($action->mHalfAutoList || preg_match('/^NBFrameObjectList$/i', get_class($this))) {
                NBFrame::using('TebleParser');
                $parser =& new NBFrameTebleParser($action->mObjectHandler->db);
                $parser->setListElements($action->mObjectHandler->mTableName, $this);
            }
        }

        function addElement($name, $caption, $width, $ext='') {
            $this->mElements[$name]['caption'] = $caption;
            $this->mElements[$name]['width'] = $width;
            $this->mElements[$name]['ext'] = $ext;
        }

        function &getListItems(&$object) {
            $items = array();
            foreach($this->mElements as $key=>$value) {
                $extItemMethod = 'extraItem_'.$key;
                $formatMethod = 'formatItem_'.$key;
                $item = array();
                $item['name'] = $key;
                $item['link'] ='';
                $item['linktitle'] ='';
                if(method_exists($this, $extItemMethod)) {
                    $item['align'] = 'left';
                    $item = array_merge($item, $this->$extItemMethod($object,$value));
                } else if(method_exists($this, $formatMethod)) {
                    $item['value'] = $this->$formatMethod($object->getVar($key));
                    $item['align'] = 'left';
                } else {
                    $item['value'] = $object->getVar($key);
                    switch ($object->vars[$key]['data_type']) {
                        case XOBJ_DTYPE_INT:
                        case XOBJ_DTYPE_FLOAT:
                            $item['align'] = 'right';
                            break;
                        default:
                            $item['align'] = 'left';
                            break;
                    }
                }
                $items[] = $item;
            }
            return $items;
        }

        function &getListHeaders($sort='', $order='') {
            $header = array();
            foreach($this->mElements as $key=>$value) {
                $head = array();
                $head['name'] = $key;
                if (isset($value['ext']['sort'])&&($value['ext']['sort']===true)) {
                    $param['list_sort']=$key;
                    if (($sort==$key)&&(strtolower($order)=='asc')) {
                        $param['list_order']='desc';
                        $head['linktitle'] = 'Descending Sort';
                        $head['style']='style="color:#00FF00"';
                    } else if (($sort==$key)&&(strtolower($order)=='desc')) {
                        $param['list_order']='asc';
                        $head['linktitle'] = 'Ascending Sort';
                        $head['style']='style="color:#FFFF00"';
                    } else {
                        $param['list_order']='asc';
                        $head['linktitle'] = 'Ascending Sort';
                        $head['style']='style="color:#DDFFDD"';
                    }
                    $head['link'] = $this->mAction->getUrl($param);
                } else {
                    $head['link'] ='';
                }
                $head['caption'] = $value['caption'];
                $head['width'] = $value['width'];
                $header[] = $head;
            }
            return $header;
        }

        function inkey($string) {
            return array_key_exists($string, $this->mElements);
        }

        function buildList(&$objects, $sort='', $order='') {
            $this->mListHeaders = $this->getListHeaders($sort, $order);
            $this->mListRecords = array();
            foreach ($objects as $object) {
                $rec['items'] =& $this->getListItems($object);
                $this->mListRecords[] = $rec;
            }
        }

        // Special List Item '__SimpleEditLink__'
        function extraItem___SimpleEditLink__(&$object,$element) {
            $key = $object->getKey();
            $keyFields = $object->getKeyFields();
            $objectKey = $keyFields[0];
            $item['link'] = $this->mAction->getUrl(array('op'=>'edit',$objectKey=>$key));
            $item['linktitle'] = 'Edit this record';
            $item['value'] = $element['ext']['caption'];
            $item['align'] = 'center';
            return $item;
        }

        // Special List Item '__SimpleDeleteLink__'
        function extraItem___SimpleDeleteLink__(&$object,$element) {
            $key = $object->getKey();
            $keyFields = $object->getKeyFields();
            $objectKey = $keyFields[0];
            $item['link'] = $this->mAction->getUrl(array('op'=>'delete',$objectKey=>$key));
            $item['linktitle'] = 'Delete this record';
            $item['value'] = $element['ext']['caption'];
            $item['align'] = 'center';
            return $item;
        }
    }
}
?>
