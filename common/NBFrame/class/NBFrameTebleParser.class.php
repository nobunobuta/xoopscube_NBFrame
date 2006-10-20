<?php
    class NBFrameTebleParser {
        var $mDB;
        var $mFields = array();
        var $mKeys = array();
        var $mPrimaryKeys = array();

        function NBFrameTebleParser(&$db) {
            $this->db_=& $db;
        }

        function setInitVars($table, &$object) {
            $this->parse($table);
            $name = false;
            foreach($this->mFields as $field) {
                $key = $field['Field'];
                $type = $this->convertXoopsObjectType($field['Type']);
                $value = "";
                
                $autoinc = (preg_match('/auto_increment/',$field['Extra'])) ? true : false;
                
                $required = ($field['Null']=="YES" || $autoinc) ? false : true;
                if ($type==XOBJ_DTYPE_TXTBOX && $field['Key']!="PRI") {
                    if (!$name) {
                        $name = $key;
                    } else {
                        if (!preg_match('/(name|title|subject)/i', $name) && preg_match('/(name|title|subject)/i', $key)) {
                            $name = $key;
                        }
                    }
                }
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
                    $object->initVar($key,$type,$value, $required, $maxlenth);
                    if ($autoinc) {
                        $object->setAutoIncrementField($key);
                    }
                }
            }
            if ($object) {
                $object->setKeyFields($this->mPrimaryKeys);
                if ($name) {
                    $object->setNameField($name);
                }
            }
        }

        function setFormElements($table, &$object) {
            $this->parse($table);
            $name = false;
            foreach($this->mFields as $field) {
                $key = $field['Field'];
                $type = $this->convertXoopsObjectType($field['Type']);
                $value = "";
                $autoinc = (preg_match('/auto_increment/',$field['Extra'])) ? true : false;
                if($autoinc) {
                    $object->addElement($key, new XoopsFormHidden($key, 0));
                } else {
                    switch ($type) {
                        case XOBJ_DTYPE_INT:
                            $maxlenth=$this->fetchSizeFromField($field['Type']);
                            $object->addElement($key, new XoopsFormText($object->__l($key), $key, 35, $maxlenth));
                            break;
                        case XOBJ_DTYPE_FLOAT:
                            $maxlenth=20;
                            $object->addElement($key, new XoopsFormText($object->__l($key), $key, 35, $maxlenth));
                            break;
                        case XOBJ_DTYPE_TXTAREA:
                            $object->addElement($key, new XoopsFormDhtmlTextArea($object->__l($key), $key, '', 8, 40));
                            break;
                        default:
                            $maxlenth=$this->fetchSizeFromField($field['Type']);
                            $object->addElement($key, new XoopsFormText($object->__l($key), $key, 35, $maxlenth));
                            break;
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
                $sql = 'SHOW FULL FIELDS FROM '.$table;
                $result2 = $this->db_->queryF($sql);

                while($row = $this->db_->fetchArray($result2)) {
                    $sFields[$table][] = $row;
                    unset($row);
                }
            }
            $this->mKeys = $sKeys[$table];
            $this->mPrimaryKeys = $sPrimaryKeys[$table];
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