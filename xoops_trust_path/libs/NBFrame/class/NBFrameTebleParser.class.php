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
    class NBFrameTebleParser {
        var $mDB;
        var $mFields = array();
        var $mKeys = array();
        var $mPrimaryKeys = array();

        function NBFrameTebleParser(&$db) {
            $this->db_=& $db;
        }

        function setHandlerProperty(&$handler) {
            $name = false;
            $parent = false;
            
            foreach($this->mFields as $field) {
                $key = $field['Field'];
                if (!preg_match('/^_NBsys_/', $key)) {
                    $type = $this->convertXoopsObjectType($field['Type']);
                    $autoinc = (preg_match('/auto_increment/',$field['Extra'])) ? true : false;
                    if ($type==XOBJ_DTYPE_TXTBOX && $field['Key']!="PRI") {
                        if (!$name) {
                            $name = $key;
                        } else if (!preg_match('/(name|title|subject)/i', $name) && preg_match('/(name|title|subject)/i', $key)) {
                            $name = $key;
                        }
                    }
                    if (is_a($handler, 'NBFrameTreeObjectHandler') && !$parent && $type==XOBJ_DTYPE_INT && $field['Key']!="PRI" && preg_match('/parent/i', $key)) {
                        $parent = $key;
                    }
                    if ($autoinc) {
                        $wk = $handler->getAutoIncrementField();
                        if (empty($wk)) {
                            $handler->setAutoIncrementField($key);
                        }
                    }
                }
            }
            if ($handler) {
                $wk = $handler->getKeyFields();
                if (empty($wk)) {
                    $handler->setKeyFields($this->mPrimaryKeys);
                }
                if ($name) {
                    $wk = $handler->getNameField();
                    if (empty($wk)) {
                        $handler->setNameField($name);
                    }
                }
                if ($parent) {
                    $wk = $handler->getParentField();
                    if (empty($wk)) {
                        $handler->setParentField($parent);
                    }
                }
            }
        }

        function setInitVars(&$object) {
            foreach($this->mFields as $field) {
                $key = $field['Field'];
                if (preg_match('/^_NBsys_/', $key)) {
                    $object->initSysFields();
                } else {
                    $type = $this->convertXoopsObjectType($field['Type']);
                    $default = "";
                    $autoinc = (preg_match('/auto_increment/',$field['Extra'])) ? true : false;
                    $required = ($field['Null']=="YES" || $autoinc) ? false : true;
                    switch ($type) {
                        case XOBJ_DTYPE_INT:
                            $default = intval($field['Default']);
                            $maxlenth=null;
                            break;
                        case XOBJ_DTYPE_FLOAT:
                            $default = floatval($field['Default']);
                            $maxlenth=null;
                            break;
                        default:
                            $default = $field['Default'];
                            $maxlenth=$this->fetchSizeFromField($field['Type']);
                            break;
                    }
                    if ($object) {
                        $object->initVar($key, $type, $default, $required, $maxlenth);
                    }
                }
            }
        }

        function setFormElements($table, &$object) {
            $this->parse($table);
            $name = false;
            foreach($this->mFields as $field) {
                $key = $field['Field'];
                if ($key == '_NBsys_update_count') {
                    $object->addVerifyFields('_NBsys_update_count');
                } else if (preg_match('/^_NBsys_/', $key)) {
                    continue;
                } else {
                    $type = $this->convertXoopsObjectType($field['Type']);
                    $value = "";
                    $autoinc = (preg_match('/auto_increment/',$field['Extra'])) ? true : false;
                    $caption = $object->__l(str_replace('_', ' ', $key));
                    if($autoinc) {
                        $object->addElement($key, new XoopsFormHidden($key, 0));
                    } else {
                        switch ($type) {
                            case XOBJ_DTYPE_INT:
                                $maxlenth=$this->fetchSizeFromField($field['Type']);
                                $object->addElement($key, new XoopsFormText($caption, $key, 35, $maxlenth));
                                break;
                            case XOBJ_DTYPE_FLOAT:
                                $maxlenth=20;
                                $object->addElement($key, new XoopsFormText($caption, $key, 35, $maxlenth));
                                break;
                            case XOBJ_DTYPE_TXTAREA:
                                $object->addElement($key, new XoopsFormDhtmlTextArea($caption, $key, '', 8, 60));
                                break;
                            default:
                                $maxlenth=$this->fetchSizeFromField($field['Type']);
                                $object->addElement($key, new XoopsFormText($caption, $key, 35, $maxlenth));
                                break;
                        }
                    }
                }
            }
        }

        function setListElements($table, &$object) {
            $this->parse($table);
            $name = false;
            $keys = array();
            foreach($this->mFields as $field) {
                $key = $field['Field'];
                if (!preg_match('/^_NBsys_/', $key)) {
                    $type = $this->convertXoopsObjectType($field['Type']);
                    $value = "";
                    $autoinc = (preg_match('/auto_increment/',$field['Extra'])) ? true : false;
                    if($autoinc) {
                        $object->addElement($key, $object->__l($key), 20, array('sort'=>true));
                    } else if ($field['Key']=="PRI") {
                        $object->addElement($key, $object->__l($key), 50, array('sort'=>true));
                    } else if ($type==XOBJ_DTYPE_TXTBOX && $field['Key']!="PRI") {
                        if (!empty($field['Key'])) {
                            $keys[] = $key;
                        } else if (!$name) {
                            $name = $key;
                        } else {
                            if (!preg_match('/(name|title|subject)/i', $name) && preg_match('/(name|title|subject)/i', $key)) {
                                $name = $key;
                            }
                        }
                    }
                }
            }
            if (!empty($keys)) {
                foreach($keys as $key) {
                    $object->addElement($key, $object->__l($key), 100, array('sort'=>true));
                }
            }
            if ($name) {
                $object->addElement($name, $object->__l($name), 250, array('sort'=>true));
            }
            $object->addElement('__SimpleEditLink__','',50, array('caption'=>$object->__l('Edit')));
            $object->addElement('__SimpleDeleteLink__','',50, array('caption'=>$object->__l('Delete')));
        }

        function parse($table) {
            static $sKeys = array(), $sPrimaryKeys = array(), $sFields = array();
            
            if (!isset($sFields[$table])) {
                $sql = 'SHOW KEYS FROM '.$table;
                $result = $this->db_->queryF($sql);
                while($row = $this->db_->fetchArray($result)) {
                    $sKeys[$table][] = $row;
                    if($row['Key_name'] == 'PRIMARY') {
                       $sPrimaryKeys[$table][$row['Seq_in_index']-1] = $row['Column_name'];
                    }
                    unset($row);
                }
                // Field
                $sql = 'SHOW COLUMNS FROM '.$table;
                $result2 = $this->db_->queryF($sql);

                while($row = $this->db_->fetchArray($result2)) {
                    $sFields[$table][] = $row;
                    unset($row);
                }
            }
            $this->mKeys = $sKeys[$table];
            if (isset($sPrimaryKeys[$table])) {
                $this->mPrimaryKeys = $sPrimaryKeys[$table];
            } else {
                $this->mPrimaryKeys =  array();
            }
            $this->mFields = $sFields[$table];
        }

        function convertXoopsObjectType($type) {
            $type = strtolower($type);
            if(preg_match("/^(\w+)/",$type,$match)) {
                if(strpos($type,"int")!==false) {
                    return XOBJ_DTYPE_INT;
                }
                if(strpos($type,"double")!==false) {
                    return XOBJ_DTYPE_FLOAT;
                }
                if(strpos($type,"float")!==false) {
                    return XOBJ_DTYPE_FLOAT;
                }
                if(strpos($type,"text")!==false) {
                    return XOBJ_DTYPE_TXTAREA;
                }
                if(strpos($type,"char")!==false) {
                    return XOBJ_DTYPE_TXTBOX;
                }
                return XOBJ_DTYPE_INT;
            }
            return false;
        }

        function fetchSizeFromField($type) {
            if(preg_match("/\(([\d]+)[,\)]$/",$type,$match)) {
                return intval($match[1]);
            }
            return null;
        }
    }

?>