<?php
if (!class_exists('NBFrame')) exit();
if (!class_exists('NBFrameObjectList')) {
    class NBFrameObjectList {
        var $mEnvironment;
        var $mAction;
        var $mElements;
        var $mDirName;
        var $mLanguage;
        var $mListHeaders;
        var $mListRecords;

        function NBFrameObjectList($environment) {
            $this->mLanguage =& NBFrame::getLanguageManager();
            $this->mEnvironment = $environment;
        }
        
        function prepare() {
        }

        function bindAction(&$action) {
            $this->mAction =& $action;
            $this->mDirName = $action->mDirName;
            
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
                    $param= 'sort='.$key;
                    $head['link'] = xoops_getenv('PHP_SELF').'?sort='.$key;
                    if (($sort==$key)&&(strtolower($order)=='asc')) {
                        $param .= '&amp;order=desc';
                        $head['linktitle'] = 'Descending Sort';
                        $head['style']='style="color:#00FF00"';
                    } else if (($sort==$key)&&(strtolower($order)=='desc')) {
                        $param .= '&amp;order=asc';
                        $head['linktitle'] = 'Ascending Sort';
                        $head['style']='style="color:#FFFF00"';
                    } else {
                        $param .= '&amp;order=asc';
                        $head['linktitle'] = 'Ascending Sort';
                        $head['style']='style="color:#DDFFDD"';
                    }
                    if (!empty($this->mAction)) {
                        $head['link'] = $this->mAction->addUrlParam($param);
                    } else {
                        $head['link'] = xoops_getenv('PHP_SELF').'?'.$param;
                    }
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
            $objectKey = $object->getKeyFields();
            $objectKey = $objectKey[0];
            $key = $object->getVar($objectKey);
            if (!empty($this->mAction)) {
                $item['link'] = $this->mAction->addUrlParam('op=edit&amp;'.$objectKey.'='.$key);
            } else {
                $item['link'] = xoops_getenv('PHP_SELF').'?op=edit&amp;'.$objectKey.'='.$key;
            }
            $item['linktitle'] = 'Edit this record';
            $item['value'] = $element['ext']['caption'];
            $item['align'] = 'center';
            return $item;
        }

        // Special List Item '__SimpleDeleteLink__'
        function extraItem___SimpleDeleteLink__(&$object,$element) {
            $objectKey = $object->getKeyFields();
            $objectKey = $objectKey[0];
            $key = $object->getVar($objectKey);
            if (!empty($this->mAction)) {
                $item['link'] = $this->mAction->addUrlParam('op=delete&amp;'.$objectKey.'='.$key);
            } else {
                $item['link'] = xoops_getenv('PHP_SELF').'?op=delete&amp;'.$objectKey.'='.$key;
            }
            $item['linktitle'] = 'Delete this record';
            $item['value'] = $element['ext']['caption'];
            $item['align'] = 'center';
            return $item;
        }
        
        function __l($msg) {
            $args = func_get_args();
            return $this->mLanguage->__l($msg, $this->mLanguage->_getParams($args));
        }

        function __e($msg) {
            $args = func_get_args();
            return $this->mLanguage->__e($msg, $this->mLanguage->_getParams($args));
        }

    }
}
?>
